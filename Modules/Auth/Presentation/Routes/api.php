<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Presentation\Controllers\AuthController;

Route::prefix('auth')->group(function () {

    // Public routes
    Route::post('/login', [AuthController::class, 'login'])
        ->name('auth.login');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('auth.logout');

        Route::get('/me', [AuthController::class, 'me'])
            ->name('auth.me');
    });
});
