<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Movement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(): Response
    {
        // ── KPI Cards ──────────────────────────────────────────────────────────
        $totalProducts = Product::count();
        $totalUsers    = User::where('role', 'USER')->count();
        $totalOrders   = Order::count();
        $pendingOrders = Order::where('status', 'PENDING')->count();

        $totalRevenue = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->whereNotIn('orders.status', ['CANCELLED'])
            ->sum(DB::raw('order_items.quantity * order_items.unit_price'));

        // ── Low-stock: aggregate per product_id first, then filter ─────────────
        // Use a subquery to avoid strict-mode GROUP BY issues
        $lowStockItems = DB::table('inventory')
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->having('total_quantity', '<', 20)
            ->get()
            ->map(function ($row) {
                $product = Product::find($row->product_id);
                return [
                    'product_id'   => $row->product_id,
                    'product_name' => $product?->product_name ?? 'Unknown',
                    'quantity'     => (int) $row->total_quantity,
                ];
            });

        // ── Orders by status breakdown ─────────────────────────────────────────
        $orderStatusBreakdown = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // ── Recent 10 orders ───────────────────────────────────────────────────
        $recentOrders = Order::orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                $user  = User::find($order->user_id);
                $total = OrderItem::where('order_id', $order->order_id)
                    ->sum(DB::raw('quantity * unit_price'));

                return [
                    'order_id'   => $order->order_id,
                    'user_name'  => $user?->name ?? 'Unknown',
                    'user_email' => $user?->email ?? '-',
                    'status'     => $order->status,
                    'total'      => number_format((float) $total, 2),
                    'created_at' => $order->created_at?->format('M j, Y g:i A') ?? '-',
                ];
            });

        // ── Top 5 selling products ─────────────────────────────────────────────
        $topProducts = OrderItem::select(
                'order_items.product_id',
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.quantity * order_items.unit_price) as revenue')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->whereNotIn('orders.status', ['CANCELLED'])
            ->groupBy('order_items.product_id')
            ->orderByDesc('units_sold')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $product = Product::find($row->product_id);
                return [
                    'product_id'   => $row->product_id,
                    'product_name' => $product?->product_name ?? 'Unknown',
                    'units_sold'   => (int) $row->units_sold,
                    'revenue'      => number_format((float) $row->revenue, 2),
                ];
            });

        // ── Recent inventory movements ─────────────────────────────────────────
        // Movement has composite PK so we use DB::table to avoid Eloquent find() issues
        $recentMovements = DB::table('movements')
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get()
            ->map(function ($m) {
                // Get current inventory quantity
                $inv = DB::table('inventory')->where('inventory_id', $m->inventory_id)->first();
                $product = $inv ? Product::find($inv->product_id) : null;

                // current quantity - delta = what it was before this movement
                $currentQty = $inv ? $inv->quantity : null;
                $prevQty    = $currentQty !== null ? $currentQty - $m->quantity_delta : null;

                return [
                    'product_name'     => $product?->product_name ?? 'Unknown',
                    'transaction_type' => $m->transaction_type,
                    'quantity_delta'   => $m->quantity_delta,
                    'prev_quantity'    => $prevQty,
                    'new_quantity'     => $currentQty,
                    'transaction_id'   => $m->transaction_id,
                    'created_at'       => $m->created_at
                        ? \Carbon\Carbon::parse($m->created_at)->format('M j, Y g:i A')
                        : '-',
                ];
            });

        // ── Warehouse capacity overview ────────────────────────────────────────
        $warehouses = Warehouse::all()->map(function ($wh) {
            $stock = (int) DB::table('inventory')
                ->where('warehouse_id', $wh->warehouse_id)
                ->sum('quantity');

            return [
                'warehouse_id'   => $wh->warehouse_id,
                'warehouse_name' => $wh->warehouse_name,
                'location'       => $wh->location,
                'city'           => $wh->city,
                'capacity'       => $wh->capacity,
                'current_stock'  => $stock,
                'utilization'    => $wh->capacity > 0
                    ? round(($stock / $wh->capacity) * 100, 1)
                    : 0,
            ];
        });

        // ── Monthly revenue (last 6 months) — use orders.created_at ───────────
        $monthlyRevenue = DB::table('order_items')
            ->select(
                DB::raw("DATE_FORMAT(orders.created_at, '%Y-%m') as month"),
                DB::raw('SUM(order_items.quantity * order_items.unit_price) as revenue')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->whereNotIn('orders.status', ['CANCELLED'])
            ->where('orders.created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('revenue', 'month');

        return Inertia::render('Dashboard', [
            'kpis' => [
                'totalProducts' => $totalProducts,
                'totalUsers'    => $totalUsers,
                'totalOrders'   => $totalOrders,
                'pendingOrders' => $pendingOrders,
                'totalRevenue'  => '$' . number_format((float) $totalRevenue, 2),
            ],
            'orderStatusBreakdown' => $orderStatusBreakdown,
            'recentOrders'         => $recentOrders,
            'topProducts'          => $topProducts,
            'lowStockItems'        => $lowStockItems,
            'recentMovements'      => $recentMovements,
            'warehouses'           => $warehouses,
            'monthlyRevenue'       => $monthlyRevenue,
        ]);
    }
}