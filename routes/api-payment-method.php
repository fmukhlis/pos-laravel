<?php

use App\Http\Controllers\API\V1\PaymentMethod\GetPaymentMethodController;
use App\Http\Controllers\API\V1\PaymentMethod\ManagePaymentMethodController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/v1/stores/{store}/payment-methods', [GetPaymentMethodController::class, 'getAll']);
    Route::post('/v1/stores/{store}/payment-methods', [ManagePaymentMethodController::class, 'create']);
    Route::get('/v1/stores/{store}/payment-methods/{paymentMethod}', [GetPaymentMethodController::class, 'get']);
    Route::put('/v1/stores/{store}/payment-methods/{paymentMethod}', [ManagePaymentMethodController::class, 'update']);
    Route::delete('/v1/stores/{store}/payment-methods/{paymentMethod}', [ManagePaymentMethodController::class, 'delete']);
});
