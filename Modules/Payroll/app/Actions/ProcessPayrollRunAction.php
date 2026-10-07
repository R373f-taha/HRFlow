<?php

namespace Modules\Payroll\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Employees\Models\Employee;
use Modules\Payroll\Models\PayrollRun;
use Modules\Payroll\Models\Payslip;
use Modules\Payroll\Services\V1\PayrollService;

class ProcessPayrollRunAction
{
    public function __construct(protected PayrollService $payrollService) {}

    public function __invoke(PayrollRun $payrollRun): PayrollRun
    {
        if ($payrollRun->status === 'finalized') {
            throw new \DomainException('Cannot process a finalized payroll run.');
        }

        DB::transaction(function () use ($payrollRun) {
            // Get all active employees with their salary structures
            $employees = Employee::with(['salaryStructure'])->get();

            foreach ($employees as $employee) {
                $salary = $employee->salaryStructure;

                $basicSalary = $salary ? (float) $salary->basic_salary : 0.00;
                $totalAllowances = $salary ? (
                    (float) $salary->housing_allowance +
                    (float) $salary->transport_allowance +
                    (float) $salary->other_allowances
                ) : 0.00;

                // Example placeholder for unpaid leaves computation from Attendance/Leaves module
                $unpaidLeaveDeduction = 0.00;

                $netSalary = ($basicSalary + $totalAllowances) - $unpaidLeaveDeduction;

                Payslip::updateOrCreate(
                    [
                        'payroll_run_id' => $payrollRun->id,
                        'employee_id' => $employee->id,
                    ],
                    [
                        'basic_salary' => $basicSalary,
                        'total_allowances' => $totalAllowances,
                        'total_deductions' => 0.00,
                        'unpaid_leave_deduction' => $unpaidLeaveDeduction,
                        'net_salary' => $netSalary,
                    ]
                );
            }

            $payrollRun->update([
                'status' => 'processed',
                'processed_at' => now(),
            ]);

            $this->payrollService->flushCache();
        });

        return $payrollRun->fresh(['payslips']);
    }
}
