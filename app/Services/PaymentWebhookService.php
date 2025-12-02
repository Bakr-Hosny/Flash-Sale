<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaymentWebhookService
{

    public function handleWebhook($request)
    {
        $cacheKey = "webhook_{$request['idempotency_key']}";

        // If webhook already processed → return same stored response
        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        $lock = Cache::lock("lock_{$cacheKey}", 10);

        if (!$lock->get()) {
            return response()->json([
                'status' => 'locked',
                'message' => 'Duplicate webhook attempt, please wait',
            ], 429);
        }

        try {
            // Fetch order
            $order = Order::lockForUpdate()
                ->with(['hold', 'product'])
                ->find($request['order_id']);

            if ($order->isPaid()) {
                $response = [
                    'status' => 'already_processed',
                    'message' => 'Order already paid',
                    'order_id' => $order->id,
                    'current_status' => $order->status,
                ];

                Cache::put($cacheKey, $response, now()->addMinutes(5));
                return response()->json($response) ;
            }

            if ($request['status'] === 'success') {

                $order->markAsPaid($request['payment_reference']);

                Log::info('Payment successful via webhook', [
                    'order_id' => $order->id,
                    'payment_reference' => $request['payment_reference'],
                ]);

                $response = [
                    'status' => 'success',
                    'message' => 'Payment processed successfully',
                    'order_id' => $order->id,
                    'payment_reference' => $request['payment_reference'],
                ];

            } else {

                $order->markAsFailed();

                Log::warning('Payment failed via webhook', [
                    'order_id' => $order->id,
                    'reason' => $request['status'] ?? 'unknown',
                ]);

                $response = [
                    'status' => 'failed',
                    'message' => 'Payment failed, order cancelled',
                    'order_id' => $order->id,
                ];
            }

            Cache::put($cacheKey, $response, 86400); // Cache for 24 hours
            return response()->json($response);

        } catch (\Exception $e) {

            Cache::forget($cacheKey);

            Log::error('Error processing payment webhook', [
                'error' => $e->getMessage(),
                'order_id' => $request['order_id'],
            ]);

            throw $e;

        }
    }


}
