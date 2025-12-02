<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hold;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{

    protected OrderService $orderService;


    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    /**
     * Create order from hold
     */
    public function createOrder(Request $request): JsonResponse
    {

        $validator = \Validator::make($request->all(), [
            'hold_id' => 'required|exists:holds,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()
            ], 422);
        }

        $request = $validator->validate();

        return $this->orderService->createOrder($request);

    }
}
