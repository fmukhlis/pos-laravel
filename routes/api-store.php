<?php

use App\Http\Controllers\API\V1\Store\CreateStoreController;
use App\Http\Controllers\API\V1\Store\GetStoreController;
use App\Http\Controllers\API\V1\Store\ManageStoreController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/v1/stores', CreateStoreController::class);
    Route::get('/v1/stores/{store}', GetStoreController::class);
    Route::put('/v1/stores/{store}', [ManageStoreController::class, 'update']);
    Route::delete('/v1/stores/{store}', [ManageStoreController::class, 'destroy']);
});
