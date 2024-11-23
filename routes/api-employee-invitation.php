<?php

use App\Http\Controllers\API\V1\Employee\GetEmployeeInvitationController;
use App\Http\Controllers\API\V1\Employee\InviteEmployeeController;
use App\Http\Controllers\API\V1\Employee\ManageEmployeeInvitationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/v1/profiles/{user}/invitations', [GetEmployeeInvitationController::class, 'getIncoming']);
    Route::patch('/v1/profiles/{user}/invitations/{employeeInvite}/accept', [ManageEmployeeInvitationController::class, 'accept']);
    Route::patch('/v1/profiles/{user}/invitations/{employeeInvite}/decline', [ManageEmployeeInvitationController::class, 'decline']);

    Route::get('/v1/stores/{store}/invitations', [GetEmployeeInvitationController::class, 'getOutgoing']);
    Route::post('/v1/stores/{store}/invitations', [InviteEmployeeController::class, 'invite']);
    Route::delete('/v1/stores/{store}/invitations/{employeeInvite}', [InviteEmployeeController::class, 'disinvite']);
});
