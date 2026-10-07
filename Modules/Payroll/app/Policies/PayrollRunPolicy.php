<?php

namespace Modules\Payroll\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Auth\Models\User;
use Modules\Payroll\Models\PayrollRun;
use Modules\Payroll\Models\Payslip;

class PayrollRunPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('payroll-runs.view');
    }

    public function create(User $user): bool
    {
        return $user->can('payroll-runs.create');
    }

    public function process(User $user, PayrollRun $payrollRun): bool
    {
        // if ($payrollRun->status === 'finalized') {
        //     return false;
        // }

        return $user->can('payroll-runs.process');
    }

    public function finalize(User $user, PayrollRun $payrollRun): bool
    {
        // if ($payrollRun->status === 'finalized') {
        //     return false;
        // }

        return $user->can('payroll-runs.finalize');
    }

}
