<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Electronics
            [
                'product_name' => 'Laptop Pro 15',
                'price' => 3500.00,
                'category_id' => 1,
                'sku' => 'ELEC-001',
                'description' => 'High performance laptop',
                'image_path' => 'images/products/laptop.png',
            ],
            [
                'product_name' => 'Wireless Mouse',
                'price' => 80.00,
                'category_id' => 1,
                'sku' => 'ELEC-002',
                'description' => 'Ergonomic mouse',
                'image_path' => 'images/products/wireless-mouse.png',
            ],

            // Books
            [
                'product_name' => 'Java Programming Book',
                'price' => 120.00,
                'category_id' => 2,
                'sku' => 'BOOK-001',
                'description' => 'Learn Java from scratch',
                'image_path' => 'images/products/java-textbook.jpg',
            ],
            [
                'product_name' => 'Algorithms Book',
                'price' => 150.00,
                'category_id' => 2,
                'sku' => 'BOOK-002',
                'description' => 'Advanced algorithm concepts',
                'image_path' => 'images/products/algorithms.png',
            ],

            // Furniture
            [
                'product_name' => 'Office Chair',
                'price' => 450.00,
                'category_id' => 3,
                'sku' => 'FURN-001',
                'description' => 'Comfortable ergonomic chair',
                'image_path' => 'images/products/office-chair.png',
            ],
            [
                'product_name' => 'Standing Desk',
                'price' => 900.00,
                'category_id' => 3,
                'sku' => 'FURN-002',
                'description' => 'Height adjustable desk',
                'image_path' => 'images/products/standing-desk.png',
            ],

            // Clothing
            [
                'product_name' => 'T-Shirt Basic',
                'price' => 35.00,
                'category_id' => 4,
                'sku' => 'CLOTH-001',
                'description' => 'Cotton t-shirt',
                'image_path' => 'images/products/t-shirt.png',
            ],
            [
                'product_name' => 'Jeans Slim Fit',
                'price' => 120.00,
                'category_id' => 4,
                'sku' => 'CLOTH-002',
                'description' => 'Modern slim jeans',
                'image_path' => 'images/products/jeans.png',
            ],

            // Food
            [
                'product_name' => 'Instant Noodles',
                'price' => 5.00,
                'category_id' => 5,
                'sku' => 'FOOD-001',
                'description' => 'Quick meal noodles',
                'image_path' => 'images/products/instant-noodles.png',
            ],
            [
                'product_name' => 'Chocolate Bar',
                'price' => 8.00,
                'category_id' => 5,
                'sku' => 'FOOD-002',
                'description' => 'Sweet chocolate snack',
                'image_path' => 'images/products/chocolate-bar.png',
            ],

            // Other
            [
                'product_name' => 'Oguri Plushie',
                'price' => 159.00,
                'category_id' => 6,
                'sku' => 'OTHE-001',
                'description' => 'None other than the fatty',
                'image_path' => 'images/products/oguri.png',
            ],
            [
                'product_name' => 'Tamamo Cross Plushie',
                'price' => 157.00,
                'category_id' => 6,
                'sku' => 'OTHE-002',
                'description' => 'A legend but also a brokie',
                'image_path' => 'images/products/tamamo.png',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
