<?php

use App\Http\Controllers\Store\CreateStoreController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/v1/stores', CreateStoreController::class);
});
