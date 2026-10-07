<?php

namespace Modules\Payroll\Actions;

use DomainException;
use Modules\Payroll\Events\PayrollFinalizedEvent;
use Modules\Payroll\Models\PayrollRun;
use Modules\Payroll\Services\V1\PayrollService;

class FinalizePayrollRunAction
{
    public function __construct(protected PayrollService $payrollService) {}

    public function __invoke(PayrollRun $payrollRun): PayrollRun
    {
        if ($payrollRun->status === 'finalized') {
            throw new DomainException('Payroll run is already finalized.');
        }

        if ($payrollRun->status !== 'processed') {
            throw new DomainException('Payroll run must be processed before it can be finalized.');
        }

        if ($payrollRun->payslips()->count() === 0) {
            throw new DomainException('Cannot finalize a payroll run with no generated payslips.');
        }

        $hasInvalidPayslips = $payrollRun->payslips()
            ->where('net_salary', '<', 0)
            ->exists();

        if ($hasInvalidPayslips) {
            throw new DomainException('Cannot finalize payroll run: One or more payslips have a negative net salary.');
        }

        $payrollRun->update([
            'status' => 'finalized',
            'finalized_at' => now(),
        ]);

        $this->payrollService->flushCache();

        PayrollFinalizedEvent::dispatch($payrollRun);

        return $payrollRun;
    }
}
