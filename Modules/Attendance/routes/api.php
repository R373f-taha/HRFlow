<?php

use Illuminate\Support\Facades\Route;
use Modules\Attendance\Http\Controllers\V1\AttendanceController;
use Modules\Attendance\Http\Controllers\V1\AttendanceSummaryController;
use Modules\Attendance\Http\Controllers\V1\MyAttendanceController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Specific custom endpoints MUST be defined before resource routes
    Route::get('attendances/my', MyAttendanceController::class)->name('attendances.my');
    Route::get('attendances/summary', AttendanceSummaryController::class)->name('attendances.summary');

    // Standard RESTful routes
    Route::apiResource('attendances', AttendanceController::class)->only([
        'index',
        'store',
        'show',
        'update',
    ]);
});
