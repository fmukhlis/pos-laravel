<?php

use App\Http\Controllers\API\V1\Order\DeleteOrderController;
use App\Http\Controllers\API\V1\Order\GetOrderController;
use App\Http\Controllers\API\V1\Order\MakeOrderController;
use App\Http\Controllers\API\V1\Order\UpdateOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/v1/stores/{store}/orders', [GetOrderController::class, 'getAll']);
    Route::post('/v1/stores/{store}/orders', MakeOrderController::class);
    Route::get('/v1/stores/{store}/orders/{order}', [GetOrderController::class, 'get']);
    Route::put('/v1/stores/{store}/orders/{order}', UpdateOrderController::class);
    Route::delete('/v1/stores/{store}/orders/{order}', [DeleteOrderController::class, 'delete']);
    Route::patch('/v1/stores/{store}/orders/{order}/cancel', [DeleteOrderController::class, 'cancel']);
    Route::patch('/v1/stores/{store}/orders/{order}/refund', [DeleteOrderController::class, 'refund']);
});
