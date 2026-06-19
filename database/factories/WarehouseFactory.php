<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Warehouse>
 */
class WarehouseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'warehouse_name' => fake()->company(),
            'location' => fake()->address(),
            'city' => fake()->city(),
            'capacity' => fake()->numberBetween(1000, 5000),
        ];
    }
}
