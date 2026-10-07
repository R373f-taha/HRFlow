<?php

namespace Modules\Payroll\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Payroll\Models\Payslip;
use Modules\Payroll\Models\PayslipDeduction;
use Modules\Payroll\Services\V1\PayrollService;

class AddPayslipDeductionAction
{
    public function __construct(protected PayrollService $payrollService) {}

    public function __invoke(Payslip $payslip, array $data): PayslipDeduction
    {
        if ($payslip->payrollRun->status === 'finalized') {
            throw new \DomainException('Cannot add deductions to a finalized payslip.');
        }

        return DB::transaction(function () use ($payslip, $data) {
            $deduction = $payslip->deductions()->create([
                'name' => $data['name'],
                'reason' => $data['reason'] ?? null,
                'amount' => $data['amount'],
            ]);

            // Recalculate total deductions & net salary on payslip
            $totalDeductions = $payslip->deductions()->sum('amount');
            $netSalary = ($payslip->basic_salary + $payslip->total_allowances) - ($totalDeductions + $payslip->unpaid_leave_deduction);

            $payslip->update([
                'total_deductions' => $totalDeductions,
                'net_salary' => $netSalary,
            ]);

            $this->payrollService->flushCache();

            return $deduction;
        });
    }
}
