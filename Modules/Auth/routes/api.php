<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\V1\AuthController;



Route::prefix('v1/auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Routes
    |--------------------------------------------------------------------------
    |
    | These endpoints do not require an authentication token.
    |
    */

    Route::post('login', [AuthController::class, 'login'])
        ->name('auth.login');

    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])
        ->name('auth.forgot-password');

    Route::post('reset-password', [AuthController::class, 'resetPassword'])
        ->name('password.reset');


    /*
    |--------------------------------------------------------------------------
    | Protected Routes
    |--------------------------------------------------------------------------
    |
    | These endpoints require a valid Sanctum Bearer token.
    |
    */

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('logout', [AuthController::class, 'logout'])
            ->name('auth.logout');

        Route::get('me', [AuthController::class, 'me'])
            ->name('auth.me');

        Route::put('password', [AuthController::class, 'changePassword'])
            ->name('auth.password');
    });
});

