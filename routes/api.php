<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\HoldController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentWebhookController;

// Product endpoints
//Route::get('/products/{id}', [ProductController::class, 'show'])
//    ->name('products.show');



Route::get('getProductById', [ProductController::class, 'getProductById']);


// Hold endpoints
Route::post('holdProduct', [HoldController::class, 'holdProduct']);


// Order endpoints
Route::post('createOrder', [OrderController::class, 'createOrder']);


// Payment webhook (idempotent)
Route::post('handleWebhook', [PaymentWebhookController::class, 'handleWebhook']);
