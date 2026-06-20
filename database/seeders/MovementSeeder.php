<?php

namespace Database\Seeders;

use App\Models\Adjustment;
use App\Models\GoodsReceipt;
use App\Models\Inventory;
use App\Models\Movement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReceiptItem;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class MovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transactions = Transaction::all();

        foreach($transactions as $transaction) {
            switch ($transaction->type) {
                case 'ORDER':
                    $order = Order::where('order_id', $transaction->transaction_id)->first();
                    $order_items = OrderItem::where('order_id', $order->order_id)->get();

                    foreach ($order_items as $order_item) {
                        $inventory = Inventory::where('product_id', $order_item->product_id)->first();
                        $transaction_type = $order->status === "CANCELLED" ? "ORDER_CANCELLATION" : "ORDER";
                        $sign = $order->status === "CANCELLED" ? 1 : -1;

                        Movement::create([
                            'inventory_id' => $inventory->inventory_id,
                            'transaction_id' => $transaction->transaction_id,
                            'transaction_type' => $transaction_type,
                            'quantity_delta' => $order_item->quantity * $sign,
                        ]);
                    }
                    break;
                case 'GOODS_RECEIPT':
                    $goods_receipt = GoodsReceipt::where('receipt_id', $transaction->transaction_id)->first();
                    $receipt_items = ReceiptItem::where('receipt_id', $goods_receipt->receipt_id)->get();

                    foreach ($receipt_items as $receipt_item) {
                        $inventory = Inventory::where('product_id', $receipt_item->product_id)->first();

                        Movement::create([
                            'inventory_id' => $inventory->inventory_id,
                            'transaction_id' => $transaction->transaction_id,
                            'transaction_type' => 'GOODS_RECEIPT',
                            'quantity_delta' => $receipt_item->quantity,
                        ]);
                    }
                    break;
                case 'ADJUSTMENT':
                    $adjustment = Adjustment::where('adjustment_id', $transaction->transaction_id)->first();

                    switch ($adjustment->note) {
                        case config('notes.adjustment_notes.0'):
                            $inventory = Inventory::where('product_id', 2)->first();

                             Movement::create([
                                'inventory_id' => $inventory->inventory_id,
                                'transaction_id' => $transaction->transaction_id,
                                'transaction_type' => 'ADJUSTMENT',
                                'quantity_delta' => -7,
                            ]);

                            break;
                        case config('notes.adjustment_notes.1'):
                            $inventory = Inventory::where('product_id', 9)->first();

                             Movement::create([
                                'inventory_id' => $inventory->inventory_id,
                                'transaction_id' => $transaction->transaction_id,
                                'transaction_type' => 'ADJUSTMENT',
                                'quantity_delta' => -8,
                            ]);

                            break;
                        case config('notes.adjustment_notes.2'):
                            $inventory = Inventory::where('product_id', 11)->first();

                             Movement::create([
                                'inventory_id' => $inventory->inventory_id,
                                'transaction_id' => $transaction->transaction_id,
                                'transaction_type' => 'ADJUSTMENT',
                                'quantity_delta' => -1,
                            ]);
                            break;
                    }
                    break;
            };
        }
    }
}
