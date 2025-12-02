<?php

namespace App\Services;

use App\Models\Hold;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class HoldService
{

    public function holdProduct($request)
    {
        $product = Product::lockForUpdate()->find($request['product_id']);

        if ($product->stock < $request['quantity']) {
            Log::warning('Insufficient stock for hold', [
                'product_id' => $product->id,
                'requested' => $request['quantity'],
                'available' => $product->available_stock,
            ]);

            return response()->json([
                'error' => 'Insufficient stock available',
                'available_stock' => $product->available_stock,
            ], 422);
        }

        // Reduce available stock
        $product->decrement('stock', $request['quantity']);

        $hold = Hold::create([
            'product_id' => $product->id,
            'quantity' => $request['quantity'],
            'expires_at' => now()->addMinutes(2),
        ]);

        Log::info('Hold created successfully', [
            'hold_id' => $hold->id,
            'product_id' => $product->id,
            'quantity' => $request['quantity'],
        ]);

        return response()->json([
            'message' => 'Product hold successfully',
            'hold_id' => $hold->id,
            'product_id' => $product->id,
            'quantity' => $request['quantity'],
            'expires_at' => $hold->expires_at,
        ]);



    }

}
