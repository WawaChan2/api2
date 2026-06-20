<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = Order::pluck('order_id');
        $products = Product::all();

        foreach ($orders as $orderId) {
            $itemCount = rand(1, 5);

            $selectedProducts = $products->random($itemCount);

            foreach ($selectedProducts as $product) {
                OrderItem::create([
                    'order_id' => $orderId,
                    'product_id' => $product->product_id,
                    'quantity' => rand(1, 10),
                    'unit_price' => $product->price,
                ]);
            }
        }
    }
}
