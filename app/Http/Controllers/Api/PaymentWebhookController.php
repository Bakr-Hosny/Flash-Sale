<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    protected PaymentWebhookService $paymentWebhookService;

    public function __construct(PaymentWebhookService $paymentWebhookService)
    {
        $this->paymentWebhookService = $paymentWebhookService;
    }
    /**
     * Handle payment webhook (idempotent)
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'status' => 'required|in:success,failed',
            'payment_reference' => 'required_if:status,success|string|nullable',
            'idempotency_key' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()
            ], 422);
        }

        $request = $validator->validate();

        return $this->paymentWebhookService->handleWebhook($request);

    }
}
