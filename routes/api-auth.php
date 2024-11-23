<?php

use App\Http\Controllers\API\V1\Auth\AuthenticatedSessionController;
use App\Http\Controllers\API\V1\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\API\V1\Auth\NewPasswordController;
use App\Http\Controllers\API\V1\Auth\PasswordResetLinkController;
use App\Http\Controllers\API\V1\Auth\RegisteredUserController;
use App\Http\Controllers\API\V1\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/register', [RegisteredUserController::class, 'store']);
Route::post('/v1/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/v1/forgot-password', [PasswordResetLinkController::class, 'store']);
Route::post('/v1/reset-password', [NewPasswordController::class, 'store']);

Route::post('/v1/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth:sanctum');

Route::post('/v1/verify-email', VerifyEmailController::class)
    ->middleware('auth:sanctum');

Route::post('/v1/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth:sanctum', 'throttle:1,1']);
