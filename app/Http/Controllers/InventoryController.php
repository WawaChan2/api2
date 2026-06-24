<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\Inventory;
use App\Models\Movement;
use App\Models\Product;
use App\Models\ReceiptItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 20;

    public function index(): Response
    {
        $stockLevels = $this->getStockLevels();
        $movements   = $this->getMovements();

        return Inertia::render('Inventory', [
            'stockLevels' => $stockLevels,
            'movements'   => $movements,
        ]);
    }

    public function restock(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,product_id',
            'quantity'   => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $transaction = Transaction::create();

            $receipt = GoodsReceipt::create([
                'receipt_id' => $transaction->transaction_id,
            ]);

            ReceiptItem::create([
                'receipt_id' => $receipt->receipt_id,
                'product_id' => $request->product_id,
                'quantity'   => $request->quantity,
                'unit_cost'  => 0,
            ]);

            $inventory = Inventory::where('product_id', $request->product_id)->first();

            if (!$inventory) {
                $warehouseId = DB::table('warehouses')->value('warehouse_id');
                if (!$warehouseId) {
                    abort(500, 'No warehouse configured.');
                }
                $inventory = Inventory::create([
                    'product_id'   => $request->product_id,
                    'warehouse_id' => $warehouseId,
                    'quantity'     => 0,
                ]);
            }

            $inventory->increment('quantity', $request->quantity);

            Movement::create([
                'inventory_id'     => $inventory->inventory_id,
                'transaction_id'   => $transaction->transaction_id,
                'transaction_type' => 'GOODS_RECEIPT',
                'quantity_delta'   => $request->quantity,
            ]);
        });

        // Check new stock level after restock
        $newQty = (int) DB::table('inventory')
            ->where('product_id', $request->product_id)
            ->sum('quantity');

        $product = Product::find($request->product_id);
        $name    = $product?->product_name ?? 'Product';

        if ($newQty < self::LOW_STOCK_THRESHOLD) {
            Inertia::flash('toast', [
                'type'    => 'warning',
                'message' => "{$name} restocked to {$newQty} units — still below threshold (" . self::LOW_STOCK_THRESHOLD . ").",
            ]);
        } else {
            Inertia::flash('toast', [
                'type'    => 'success',
                'message' => "{$name} restocked successfully. New stock: {$newQty} units.",
            ]);
        }

        return back();
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function getStockLevels(): \Illuminate\Support\Collection
    {
        return DB::table('inventory')
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->get()
            ->map(function ($row) {
                $product = Product::find($row->product_id);
                return [
                    'product_id'   => $row->product_id,
                    'product_name' => $product?->product_name ?? 'Unknown',
                    'quantity'     => (int) $row->total_quantity,
                ];
            })
            ->sortBy('product_name')
            ->values();
    }

    private function getMovements(): \Illuminate\Support\Collection
    {
        // ── Step 1: display rows (newest 50) ──────────────────────────────────
        $rows = DB::table('movements')
            ->orderBy('created_at', 'desc')
            ->orderBy('transaction_id', 'desc')
            ->limit(50)
            ->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        // ── Step 2: resolve inventory → product mapping ───────────────────────
        $inventoryIds = $rows->pluck('inventory_id')->unique()->values();

        $inventories = DB::table('inventory')
            ->whereIn('inventory_id', $inventoryIds)
            ->get()
            ->keyBy('inventory_id');

        // product_id for each inventory_id
        $invToProduct = $inventories->mapWithKeys(fn ($inv) => [
            $inv->inventory_id => $inv->product_id,
        ]);

        // unique product_ids touched by the display rows
        $productIds = $invToProduct->values()->unique()->values();

        // ── Step 3: live product-level totals (sum across all warehouses) ─────
        $liveTotals = DB::table('inventory')
            ->whereIn('product_id', $productIds)
            ->select('product_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('product_id')
            ->pluck('total', 'product_id')
            ->map(fn ($v) => (int) $v);

        // ── Step 4: ALL movements for those products (oldest first) ───────────
        // We need every movement for each product (across all its inventory rows)
        // so we can replay the product-level delta timeline.
        $allInvIdsForProducts = DB::table('inventory')
            ->whereIn('product_id', $productIds)
            ->pluck('inventory_id');

        $allMovements = DB::table('movements')
            ->whereIn('inventory_id', $allInvIdsForProducts)
            ->orderBy('created_at', 'asc')
            ->orderBy('transaction_id', 'asc')
            ->get();

        // group by product_id (resolved via inventory)
        $allInvToProduct = DB::table('inventory')
            ->whereIn('inventory_id', $allInvIdsForProducts)
            ->pluck('product_id', 'inventory_id');

        $movementsByProduct = $allMovements->groupBy(
            fn ($m) => $allInvToProduct[$m->inventory_id] ?? null
        );

        // ── Step 5: walk backwards per product to build snapshots ─────────────
        // Key: "{inventory_id}_{transaction_id}" → ['prev' => int, 'new' => int]
        $snapshots = [];

        foreach ($movementsByProduct as $productId => $productMovements) {
            if (!$productId) continue;

            $runningTotal = $liveTotals[$productId] ?? 0;

            // Reverse: newest first for backwards walk
            foreach ($productMovements->reverse()->values() as $m) {
                $newQty  = $runningTotal;
                $prevQty = $runningTotal - $m->quantity_delta;

                $snapshots[$m->inventory_id . '_' . $m->transaction_id] = [
                    'prev' => $prevQty,
                    'new'  => $newQty,
                ];

                $runningTotal = $prevQty;
            }
        }

        // ── Step 6: product name cache ────────────────────────────────────────
        $productNames = Product::whereIn('product_id', $productIds)
            ->pluck('product_name', 'product_id');

        // ── Step 7: map display rows ──────────────────────────────────────────
        return $rows->map(function ($m) use ($snapshots, $invToProduct, $productNames) {
            $key       = $m->inventory_id . '_' . $m->transaction_id;
            $snap      = $snapshots[$key] ?? null;
            $productId = $invToProduct[$m->inventory_id] ?? null;

            return [
                'product_name'     => $productId ? ($productNames[$productId] ?? 'Unknown') : 'Unknown',
                'transaction_type' => $m->transaction_type,
                'quantity_delta'   => $m->quantity_delta,
                'prev_quantity'    => $snap['prev'] ?? null,
                'new_quantity'     => $snap['new']  ?? null,
                'transaction_id'   => $m->transaction_id,
                'created_at'       => $m->created_at
                    ? \Carbon\Carbon::parse($m->created_at)->format('n/j/Y, g:i:s A')
                    : '-',
            ];
        });
    }
}