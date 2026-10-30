<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\V1\GetAttendanceOverviewReportController;
use Modules\Reports\Http\Controllers\V1\GetHeadcountReportController;
use Modules\Reports\Http\Controllers\V1\GetLeaveUtilizationReportController;
use Modules\Reports\Http\Controllers\V1\GetPayrollSummaryReportController;
use Modules\Reports\Http\Controllers\V1\GetPerformanceSummaryReportController;
use Modules\Reports\Http\Controllers\V1\GetTurnoverReportController;

Route::middleware(['auth:sanctum'])->prefix('v1/reports')->group(function () {
    Route::get('/headcount', GetHeadcountReportController::class);
    Route::get('/payroll-summary', GetPayrollSummaryReportController::class);
    Route::get('/leave-utilization', GetLeaveUtilizationReportController::class);
    Route::get('/attendance-overview', GetAttendanceOverviewReportController::class);
    Route::get('/turnover', GetTurnoverReportController::class);
    Route::get('/performance-summary', GetPerformanceSummaryReportController::class);
});
