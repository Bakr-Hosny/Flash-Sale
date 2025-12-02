<?php

namespace Tests\Feature;

use App\Models\Hold;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class FlashSaleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function parallel_holds_do_not_oversell()
    {
        // Product stock = 5
        $product = Product::factory()->create(['stock' => 5]);

        // Run 10 parallel attempts (simulate race condition)
        $attempts = 10;
        $results = [];

        DB::beginTransaction();
        for ($i = 0; $i < $attempts; $i++) {
            $results[] = $this->postJson('/api/holdProduct', [
                'product_id' => $product->id,
                'quantity' => 1
            ]);
        }
        DB::commit();

        // Count successful holds
        $successCount = 0;
        foreach ($results as $response) {
            if ($response->status() === 200) {
                $successCount++;
            }
        }

        // 5 only should succeed
        $this->assertEquals(5, $successCount);

        // No oversell
        $product->refresh();
        $this->assertEquals(0, $product->stock);
    }

    /** @test */
    public function expired_holds_return_stock()
    {
        $product = Product::factory()->create(['stock' => 10]);

        // Create expired hold
        $hold = Hold::factory()->create([
            'product_id' => $product->id,
            'quantity' => 3,
            'expires_at' => now()->subMinutes(5), // expired
            'order_id' => null,
        ]);

        // Run command
        Artisan::call('holds:release-expired');

        $product->refresh();

        // Stock returned properly
        $this->assertEquals(13, $product->stock);
        $this->assertDatabaseMissing('holds', ['id' => $hold->id]);
    }

    /** @test */
    public function webhook_is_idempotent()
    {
        $order = Order::factory()->create([
            'status' => Order::STATUS_PENDING
        ]);

        $key = Str::uuid()->toString();

        // First call (success)
        $first = $this->postJson('/api/handleWebhook', [
            'order_id' => $order->id,
            'status' => 'success',
            'payment_reference' => 'REF123',
            'idempotency_key' => $key
        ]);

        // Second call with SAME key → should return same cached response
        $second = $this->postJson('/api/handleWebhook', [
            'order_id' => $order->id,
            'status' => 'success',
            'payment_reference' => 'REF123',
            'idempotency_key' => $key
        ]);

        $this->assertEquals($first->json(), $second->json());

        // Ensure order marked as PAID only once
        $order->refresh();
        $this->assertEquals(Order::STATUS_PAID, $order->status);
    }

    /** @test */
    public function webhook_arrives_before_order_creation()
    {
        $order = Order::factory()->make(['id' => 555]); // Not saved in DB

        $key = Str::uuid();

        // Send webhook BEFORE order exists
        $response = $this->postJson('/api/handleWebhook', [
            'order_id' => 555,
            'status' => 'success',
            'payment_reference' => 'XYZ',
            'idempotency_key' => $key,
        ]);

        // Should return validation 422 (order not exists)
        $response->assertStatus(422);

        // Later create the actual order
        $realOrder = Order::factory()->create(['id' => 555]);

        // Try webhook again → should pass now
        $response2 = $this->postJson('/api/handleWebhook', [
            'order_id' => 555,
            'status' => 'success',
            'payment_reference' => 'XYZ',
            'idempotency_key' => $key,
        ]);

        $response2->assertStatus(200);
        $realOrder->refresh();

        $this->assertEquals(Order::STATUS_PAID, $realOrder->status);
    }


}
