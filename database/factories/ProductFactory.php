<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Test Product ' . $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
            'price' => 100,
            'stock' => 10,
            'original_stock' => 10,
            'is_flash_sale' => true,
        ];
    }
}
