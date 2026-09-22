<?php

use Illuminate\Support\Facades\Route;
use Modules\Employees\Http\Controllers\V1\EmployeeDocumentController;
//use Modules\Employees\Http\Controllers\V1\EmployeeDocumentController;
use Modules\Employees\Http\Controllers\V1\EmployeeSalaryController;
use Modules\Employees\Http\Controllers\V1\EmployeesController;
use Modules\Employees\Http\Controllers\V1\ProfileController;



Route::middleware(['auth:sanctum'])->prefix('v1/employees')->group(function () {


// Profile Endpoints for Authenticated Employee (/v1/employees/me)
    Route::prefix('/me')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
    });



    // Standard Employee CRUD & Actions
    Route::get('/', [EmployeesController::class, 'index']);
    Route::post('/', [EmployeesController::class, 'store']);
    Route::get('/{employee}', [EmployeesController::class, 'show']);
    Route::put('/{employee}', [EmployeesController::class, 'update']);
    Route::post('/{employee}/terminate', [EmployeesController::class, 'terminate']);

    // Salary Sub-resource Routes
    Route::get('/{employee}/salary-history', [EmployeeSalaryController::class, 'history']);


    // Document Sub-resource Routes
    Route::get('/{employee}/documents', [EmployeeDocumentController::class, 'index']);
    Route::post('/{employee}/documents', [EmployeeDocumentController::class, 'store']);
});



