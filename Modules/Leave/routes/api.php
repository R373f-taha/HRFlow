<?php

use Illuminate\Support\Facades\Route;
use Modules\Leave\Http\Controllers\V1\ApproveLeaveRequestController;
use Modules\Leave\Http\Controllers\V1\LeaveBalanceController;
use Modules\Leave\Http\Controllers\V1\LeaveRequestController;
use Modules\Leave\Http\Controllers\V1\LeaveTypeController;
use Modules\Leave\Http\Controllers\V1\RejectLeaveRequestController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Leave Types Endpoints
    |--------------------------------------------------------------------------
    */
    Route::prefix('leave-types')->group(function () {
        Route::get('/', [LeaveTypeController::class, 'index']);
        Route::post('/', [LeaveTypeController::class, 'store']);
        Route::put('/{leaveType}', [LeaveTypeController::class, 'update']);
    });

    /*
    |--------------------------------------------------------------------------
    | Leave Balances Endpoints
    |--------------------------------------------------------------------------
    */
    Route::get('/leave-requests/balance', [LeaveBalanceController::class, 'myBalance']);
    Route::get('/employees/{employee}/leave-balance', [LeaveBalanceController::class, 'employeeBalance']);

    /*
    |--------------------------------------------------------------------------
    | Leave Requests Endpoints
    |--------------------------------------------------------------------------
    */
    Route::prefix('leave-requests')->group(function () {
        Route::get('/', [LeaveRequestController::class, 'index']);
        Route::post('/', [LeaveRequestController::class, 'store']);
        Route::get('/{leaveRequest}', [LeaveRequestController::class, 'show']);
        Route::delete('/{leaveRequest}', [LeaveRequestController::class, 'destroy']);




        Route::patch('/{leaveRequest}/approve', ApproveLeaveRequestController::class);


        Route::patch('/{leaveRequest}/reject', RejectLeaveRequestController::class);

    });
});
