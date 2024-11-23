<?php

use App\Http\Controllers\API\V1\Employee\GetEmployeeController;
use App\Http\Controllers\API\V1\Employee\ManageEmployeeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/v1/stores/{store}/employees', GetEmployeeController::class);
    Route::patch('/v1/stores/{store}/employees/{employee}/terminate', [ManageEmployeeController::class, 'terminate']);
    Route::patch('/v1/stores/{store}/employees/{employee}/make-active', [ManageEmployeeController::class, 'makeActive']);
    Route::patch('/v1/stores/{store}/employees/{employee}/make-inactive', [ManageEmployeeController::class, 'makeInactive']);
});
