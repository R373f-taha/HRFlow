<?php

namespace Modules\Payroll\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Auth\Models\User;
use Modules\Payroll\Models\Payslip;

class PayslipPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Payslip $payslip): bool
    {
        return  $user->can('payslips.view');
    }

  public function applyDeduction(User $user, Payslip $payslip): bool
    {
        if ($user->hasRole('hr-admin')) {
            return true;
        }

        return $user->can('payslips.deductions.create');
    }
}
