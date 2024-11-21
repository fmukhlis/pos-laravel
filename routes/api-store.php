<?php

use App\Http\Controllers\API\V1\Customer\GetCustomerController;
use App\Http\Controllers\API\V1\Employee\GetEmployeeController;
use App\Http\Controllers\API\V1\Employee\GetEmployeeInvitationController;
use App\Http\Controllers\API\V1\Employee\InviteEmployeeController;
use App\Http\Controllers\API\V1\Employee\ManageEmployeeController;
use App\Http\Controllers\API\V1\Employee\ManageEmployeeInvitationController;
use App\Http\Controllers\API\V1\Store\CreateStoreController;
use App\Http\Controllers\API\V1\Store\GetStoreController;
use App\Http\Controllers\API\V1\Store\ManageStoreController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/v1/profiles/{user}/invitations', [GetEmployeeInvitationController::class, 'getIncoming']);
    Route::patch('/v1/profiles/{user}/invitations/{employeeInvite}/accept', [ManageEmployeeInvitationController::class, 'accept']);
    Route::patch('/v1/profiles/{user}/invitations/{employeeInvite}/decline', [ManageEmployeeInvitationController::class, 'decline']);

    Route::post('/v1/stores', CreateStoreController::class);
    Route::get('/v1/stores/{store}', GetStoreController::class);
    Route::put('/v1/stores/{store}', [ManageStoreController::class, 'update']);
    Route::delete('/v1/stores/{store}', [ManageStoreController::class, 'destroy']);

    Route::get('/v1/stores/{store}/invitations', [GetEmployeeInvitationController::class, 'getOutgoing']);
    Route::post('/v1/stores/{store}/invitations', [InviteEmployeeController::class, 'invite']);
    Route::delete('/v1/stores/{store}/invitations/{employeeInvite}', [InviteEmployeeController::class, 'disinvite']);

    Route::get('/v1/stores/{store}/employees', GetEmployeeController::class);
    Route::patch('/v1/stores/{store}/employees/{employee}/terminate', [ManageEmployeeController::class, 'terminate']);
    Route::patch('/v1/stores/{store}/employees/{employee}/make-active', [ManageEmployeeController::class, 'makeActive']);
    Route::patch('/v1/stores/{store}/employees/{employee}/make-inactive', [ManageEmployeeController::class, 'makeInactive']);

    Route::get('/v1/stores/{storeId}/customers', [GetCustomerController::class, 'getAll']);
    Route::get('/v1/stores/{store}/customers/{customer}', [GetCustomerController::class, 'get']);
});
