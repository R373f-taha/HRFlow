<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\V1\AuthController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('auths', AuthController::class)->names('auth');
});


Route::get('auth/reset-password/{token}', function ($token) {
    // يمكن هنا إعادة التوجيه لـ Frontend URL محدد
    return response()->json([
        'message' => 'Use this token in your POST request to reset password.',
        'token' => $token,
    ]);
})->name('password.reset');
