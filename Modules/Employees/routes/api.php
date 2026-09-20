<?php

use Illuminate\Support\Facades\Route;
use Modules\Employees\Http\Controllers\V1\EmployeeSalaryController;
use Modules\Employees\Http\Controllers\V1\EmployeesController;
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('employees', EmployeesController::class)->names('employees');
});




Route::middleware(['auth:sanctum'])->prefix('v1/employees')->group(function () {
    // Standard Employee CRUD & Actions
    Route::get('/', [EmployeesController::class, 'index']);
    Route::post('/', [EmployeesController::class, 'store']);
    Route::get('/{employee}', [EmployeesController::class, 'show']);
    Route::put('/{employee}', [EmployeesController::class, 'update']);
    Route::post('/{employee}/terminate', [EmployeesController::class, 'terminate']);

    // Salary Sub-resource Routes
    Route::get('/{employee}/salary-history', [EmployeeSalaryController::class, 'history']);
});
