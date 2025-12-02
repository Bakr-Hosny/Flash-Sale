<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductService
{

    public function getProductById(string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

}
