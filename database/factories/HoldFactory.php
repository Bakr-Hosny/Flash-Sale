<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

class HoldFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'order_id' => null,
            'quantity' => 1,
            'expires_at' => now()->addMinutes(2),
        ];
    }

    public function expired()
    {
        return $this->state(fn () => [
            'expires_at' => now()->subMinutes(5),
        ]);
    }
}
