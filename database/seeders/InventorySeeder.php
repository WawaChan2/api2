<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productIds = Product::pluck('product_id');
        $warehouseIds = Warehouse::pluck('warehouse_id');

        $allPairs = [];
        foreach ($productIds as $productId) {
            foreach ($warehouseIds as $warehouseId) {
                $allPairs[] = [
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                ];
            }
        }

        shuffle($allPairs);
        $subsetSize = (int) (count($allPairs) * 0.7);
        $selected = array_slice($allPairs, 0, $subsetSize);

        foreach ($selected as $combo) {
            Inventory::factory()->create([
                'product_id' => $combo['product_id'],
                'warehouse_id' => $combo['warehouse_id'],
            ]);
        }
    }
}
