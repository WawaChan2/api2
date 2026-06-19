<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Electronics',
            'Books',
            'Furniture',
            'Clothing',
            'Food',
            'Others'
        ];

        foreach ($categories as $category_name) {
            Category::create([
                'category_name' => $category_name,
            ]);
        }
    }
}
