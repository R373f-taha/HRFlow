
<?php

use Illuminate\Support\Facades\Route;
use Modules\Payroll\Http\Controllers\V1\FinalizePayrollRunController;
use Modules\Payroll\Http\Controllers\V1\PayrollRunController;
use Modules\Payroll\Http\Controllers\V1\PayrollRunPayslipController;
use Modules\Payroll\Http\Controllers\V1\PayslipController;
use Modules\Payroll\Http\Controllers\V1\ProcessPayrollRunController;
use Modules\Payroll\Http\Controllers\V1\SalaryStructureController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    // Salary Structures (HR Admin)
    Route::get('salary-structures', [SalaryStructureController::class, 'index']);
    Route::post('salary-structures', [SalaryStructureController::class, 'store']);
    Route::put('salary-structures/{id}', [SalaryStructureController::class, 'update']);

    // Payroll Runs (HR Admin)
    Route::get('payroll-runs', [PayrollRunController::class, 'index']);
    Route::post('payroll-runs', [PayrollRunController::class, 'store']);
   // State Transitions (Invokable Actions)
    Route::post('payroll-runs/{id}/process', ProcessPayrollRunController::class);
    Route::post('payroll-runs/{id}/finalize', FinalizePayrollRunController::class);

    // Sub-resource Navigation
    Route::get('payroll-runs/{id}/payslips', [PayrollRunPayslipController::class, 'index']);

    // Payslips & Deductions
    Route::get('payslips/my', [PayslipController::class, 'myPayslips']);
    Route::get('payslips/{id}', [PayslipController::class, 'show']);
    Route::post('payslips/{id}/deductions', [PayslipController::class, 'addDeduction']);
});
