<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            WarehouseSeeder::class,
            CategorySeeder::class,
            TransactionSeeder::class,
            ProductSeeder::class,
            InventorySeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
            GoodsReceiptSeeder::class,
            ReceiptItemSeeder::class,
            AdjustmentSeeder::class,
            MovementSeeder::class
        ]);
    }
}
