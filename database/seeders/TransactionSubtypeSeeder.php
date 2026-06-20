<?php

namespace Database\Seeders;

use App\Models\Adjustment;
use App\Models\GoodsReceipt;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class TransactionSubtypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transactionIds = Transaction::pluck('transaction_id')->shuffle()->values();

        $total = $transactionIds->count();

        $orderCount = (int) round($total * 0.6);
        $goodsReceiptCount = (int) round($total * 0.3);
        $adjustmentCount = $total - $orderCount - $goodsReceiptCount;

        $orderIds = $transactionIds->slice(0, $orderCount);
        $goodsReceiptIds = $transactionIds->slice($orderCount, $goodsReceiptCount);
        $adjustmentIds = $transactionIds->slice($orderCount + $goodsReceiptCount, $adjustmentCount);

        foreach ($orderIds as $id) {
            Order::create([
                'order_id' => $id,
                'user_id' => User::where('role', 'USER')->inRandomOrder()->first()->id,
                'status' => Arr::random([
                    'PENDING', 
                    'SHIPPED', 
                    'DELIVERED', 
                    'CANCELLED'
                ]),
            ]);
        }

        foreach ($goodsReceiptIds as $id) {
            GoodsReceipt::create([
                'receipt_id' => $id,
            ]);
        }

        $i = 0;
        $notes = config('notes.adjustment_notes');

        foreach ($adjustmentIds as $id) {
            Adjustment::create([
                'adjustment_id' => $id,
                'note' => $notes[$i],
            ]);

            $i++;
        }
    }
}
