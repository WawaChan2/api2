<?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CartItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();

        User::all()->each(function ($user) use ($products) {
            CartItem::create([
                'user_id' => $user->id,
                'product_id' => $products->random()->product_id,
                'quantity' => rand(1, 3),
            ]);
        });
    }
}
