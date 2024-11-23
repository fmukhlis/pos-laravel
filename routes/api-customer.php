<?php

use App\Http\Controllers\API\V1\Customer\GetCustomerController;
use App\Http\Controllers\API\V1\Customer\ManageCustomerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/v1/stores/{store}/customers', [GetCustomerController::class, 'getAll']);
    Route::post('/v1/stores/{store}/customers', [ManageCustomerController::class, 'create']);
    Route::get('/v1/stores/{store}/customers/{customer}', [GetCustomerController::class, 'get']);
    Route::put('/v1/stores/{store}/customers/{customer}', [ManageCustomerController::class, 'update']);
    Route::delete('/v1/stores/{store}/customers/{customer}', [ManageCustomerController::class, 'delete']);
});
