<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/v1/user', function (Request $request) {
    return new \App\Http\Resources\V1\User($request->user());
})->middleware('auth:sanctum');

Route::get('/v1/dashboard', function (Request $request) {
    return new \App\Http\Resources\V1\User($request->user());
})->middleware(['auth:sanctum', 'verified']);


require __DIR__ . '/api-auth.php';
require __DIR__ . '/api-store.php';
