<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
use App\Models\Hold;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'hold_id' => null,
            'quantity' => 1,
            'total_price' => 100,
            'status' => 'pending',
            'payment_reference' => null,
            'idempotency_key' => null,
        ];
    }

    public function paid()
    {
        return $this->state(fn () => [
            'status' => 'paid',
            'payment_reference' => 'PAY-' . $this->faker->uuid,
        ]);
    }
}
