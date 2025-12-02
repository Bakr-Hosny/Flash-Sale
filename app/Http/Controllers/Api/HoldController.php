<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hold;
use App\Models\Product;
use App\Services\HoldService;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HoldController extends Controller
{
    protected HoldService $holdService;

    public function __construct( HoldService $holdService )  {
        $this->holdService = $holdService;
    }


    /**
     * Create a temporary hold
     */
    public function holdProduct(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()
            ], 422);
        }

        $request = $validator->validate();

        return $this->holdService->holdProduct($request);
    }
}
