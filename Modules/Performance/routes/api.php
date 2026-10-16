<?php

use Illuminate\Support\Facades\Route;
use Modules\Performance\Http\Controllers\V1\ActivatePerformanceCycleController;
use Modules\Performance\Http\Controllers\V1\ClosePerformanceCycleController;
use Modules\Performance\Http\Controllers\V1\GetEmployeePerformanceHistoryController;
use Modules\Performance\Http\Controllers\V1\GetMyPerformanceReviewsController;
use Modules\Performance\Http\Controllers\V1\PerformanceCycleController;
use Modules\Performance\Http\Controllers\V1\PerformanceReviewController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    // Performance Cycles Management
    Route::prefix('performance-cycles')->group(function () {
        Route::get('/', [PerformanceCycleController::class, 'index']);
        Route::post('/', [PerformanceCycleController::class, 'store']);
        Route::post('/{id}/activate', ActivatePerformanceCycleController::class);
        Route::post('/{id}/close', ClosePerformanceCycleController::class);
    });

    // Performance Reviews Management
    Route::prefix('performance-reviews')->group(function () {
        Route::get('/', [PerformanceReviewController::class, 'index']);
        Route::post('/', [PerformanceReviewController::class, 'store']);
        Route::get('/my', GetMyPerformanceReviewsController::class);
        Route::put('/{id}', [PerformanceReviewController::class, 'update']);
    });

    // Employee Performance History
   Route::get('employees/{id}/performance', GetEmployeePerformanceHistoryController::class);});
