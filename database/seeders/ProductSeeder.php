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
                'description' => 'Powerful 15-inch laptop featuring a fast processor, ample storage, and a vibrant display for work, study, and entertainment.',
                'image_path' => '/images/products/laptop.png',
            ],
            [
                'product_name' => 'Wireless Mouse',
                'price' => 80.00,
                'category_id' => 1,
                'sku' => 'ELEC-002',
                'description' => 'Wireless ergonomic mouse with precision tracking, responsive clicks, and long battery life for comfortable daily use.',
                'image_path' => '/images/products/wireless-mouse.png',
            ],

            // Books
            [
                'product_name' => 'Java Programming Book',
                'price' => 120.00,
                'category_id' => 2,
                'sku' => 'BOOK-001',
                'description' => 'Comprehensive beginner-friendly guide covering Java fundamentals, object-oriented programming, and practical projects.',
                'image_path' => '/images/products/java-textbook.jpg',
            ],
            [
                'product_name' => 'Algorithms Book',
                'price' => 150.00,
                'category_id' => 2,
                'sku' => 'BOOK-002',
                'description' => 'In-depth exploration of algorithms and data structures with real-world examples, analysis techniques, and optimization strategies.',
                'image_path' => '/images/products/algorithms.png',
            ],

            // Furniture
            [
                'product_name' => 'Office Chair',
                'price' => 450.00,
                'category_id' => 3,
                'sku' => 'FURN-001',
                'description' => 'Ergonomic office chair with adjustable height, lumbar support, and breathable materials designed for long working sessions.',
                'image_path' => '/images/products/office-chair.png',
            ],
            [
                'product_name' => 'Standing Desk',
                'price' => 900.00,
                'category_id' => 3,
                'sku' => 'FURN-002',
                'description' => 'Height-adjustable standing desk that promotes healthier posture and flexible work habits throughout the day.',
                'image_path' => '/images/products/standing-desk.png',
            ],

            // Clothing
            [
                'product_name' => 'T-Shirt Basic',
                'price' => 35.00,
                'category_id' => 4,
                'sku' => 'CLOTH-001',
                'description' => 'Classic cotton t-shirt made from soft, breathable fabric, suitable for everyday casual wear.',
                'image_path' => '/images/products/t-shirt.png',
            ],
            [
                'product_name' => 'Jeans Slim Fit',
                'price' => 120.00,
                'category_id' => 4,
                'sku' => 'CLOTH-002',
                'description' => 'Modern slim-fit jeans crafted for comfort and style, featuring durable denim and a versatile design.',
                'image_path' => '/images/products/jeans.png',
            ],

            // Food
            [
                'product_name' => 'Instant Noodles',
                'price' => 5.00,
                'category_id' => 5,
                'sku' => 'FOOD-001',
                'description' => 'Convenient instant noodles with rich flavor and quick preparation, perfect for busy schedules and late-night meals.',
                'image_path' => '/images/products/instant-noodles.png',
            ],
            [
                'product_name' => 'Chocolate Bar',
                'price' => 8.00,
                'category_id' => 5,
                'sku' => 'FOOD-002',
                'description' => 'Smooth and satisfying chocolate bar made with quality cocoa, ideal as a sweet snack or small treat.',
                'image_path' => '/images/products/chocolate-bar.png',
            ],

            // Other
            [
                'product_name' => 'Oguri Plushie',
                'price' => 159.00,
                'category_id' => 6,
                'sku' => 'OTHE-001',
                'description' => 'Premium Oguri Cap plushie inspired by the beloved Uma Musume character, featuring detailed embroidery and a soft, huggable design.',
                'image_path' => '/images/products/oguri.png',
            ],
            [
                'product_name' => 'Tamamo Plushie',
                'price' => 157.00,
                'category_id' => 6,
                'sku' => 'OTHE-002',
                'description' => 'Collectible Tamamo Cross plushie capturing the energetic personality of the legendary racer, made with high-quality materials.',
                'image_path' => '/images/products/tamamo.png',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
