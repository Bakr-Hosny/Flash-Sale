<?php

namespace App\Services;

use App\Models\Hold;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderService
{

    public function createOrder($request)
    {
        $hold = Hold::lockForUpdate()
            ->with('product')
            ->find($request['hold_id']);

        if (!$hold->isValid()) {
            Log::warning('Attempt to use invalid hold', [
                'hold_id' => $hold->id,
                'expired' => $hold->isExpired(),
                'has_order' => !is_null($hold->order_id),
            ]);

            return response()->json([
                'error' => 'Hold is expired or already used',
                'expired' => $hold->isExpired(),
            ], 422);
        }

        // Check if hold was already used (double-check)
        if (Order::where('hold_id', $hold->id)->exists()) {
            return response()->json([
                'error' => 'Hold already used for another order',
            ], 409);
        }

        $order = Order::create([
            'product_id' => $hold->product_id,
            'hold_id' => $hold->id,
            'quantity' => $hold->quantity,
            'total_price' => $hold->quantity * $hold->product->price,
            'status' => Order::STATUS_PENDING,
        ]);

        // Link hold to order
        $hold->update(['order_id' => $order->id]);

        Log::info('Order created from hold', [
            'order_id' => $order->id,
            'hold_id' => $hold->id,
            'amount' => $order->total_price,
        ]);

        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order,
        ], 201);
    }

}
