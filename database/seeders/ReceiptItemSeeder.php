<?php

namespace Database\Seeders;

use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\ReceiptItem;
use Illuminate\Database\Seeder;

class ReceiptItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $goods_receipts = GoodsReceipt::pluck('receipt_id');
        $products = Product::all();

        foreach ($goods_receipts as $receiptId) {
            $itemCount = rand(1, 5);

            $selectedProducts = $products->random($itemCount);

            foreach ($selectedProducts as $product) {
                ReceiptItem::create([
                    'receipt_id' => $receiptId,
                    'product_id' => $product->product_id,
                    'quantity' => rand(50, 200),
                    'unit_cost' => $product->price,
                ]);
            }
        }
    }
}
