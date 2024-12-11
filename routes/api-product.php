<?php

use App\Http\Controllers\API\V1\Product\CreateProductController;
use App\Http\Controllers\API\V1\Product\DeleteProductController;
use App\Http\Controllers\API\V1\Product\GetProductController;
use App\Http\Controllers\API\V1\Product\UpdateProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/v1/stores/{store}/products', [GetProductController::class, 'getAll']);
    Route::post('/v1/stores/{store}/products', CreateProductController::class);
    Route::get('/v1/stores/{store}/products/{product}', [GetProductController::class, 'get']);
    Route::put('/v1/stores/{store}/products/{product}', UpdateProductController::class);
    Route::delete('/v1/stores/{store}/products/{product}', DeleteProductController::class);
});
