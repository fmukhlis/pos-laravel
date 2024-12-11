<?php

use App\Http\Controllers\API\V1\ProductCategory\GetProductCategoryController;
use App\Http\Controllers\API\V1\ProductCategory\ManageProductCategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/v1/stores/{store}/product-categories', [GetProductCategoryController::class, 'getAll']);
    Route::post('/v1/stores/{store}/product-categories', [ManageProductCategoryController::class, 'create']);
    Route::get('/v1/stores/{store}/product-categories/{productCategory}', [GetProductCategoryController::class, 'get']);
    Route::put('/v1/stores/{store}/product-categories/{productCategory}', [ManageProductCategoryController::class, 'update']);
    Route::delete('/v1/stores/{store}/product-categories/{productCategory}', [ManageProductCategoryController::class, 'delete']);
});
