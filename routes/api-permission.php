<?php

use App\Http\Controllers\API\V1\Permission\GetPermissionController;
use App\Http\Controllers\API\V1\Permission\ManagePermissionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/v1/stores/{store}/permissions', [GetPermissionController::class, 'getAll']);
    Route::post('/v1/stores/{store}/permissions', [ManagePermissionController::class, 'create']);
    Route::get('/v1/stores/{store}/permissions/{permission}', [GetPermissionController::class, 'get']);
    Route::put('/v1/stores/{store}/permissions/{permission}', [ManagePermissionController::class, 'update']);
    Route::delete('/v1/stores/{store}/permissions/{permission}', [ManagePermissionController::class, 'delete']);
});
