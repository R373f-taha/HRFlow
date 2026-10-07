<?php

namespace Modules\Payroll\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Auth\Models\User;
use Modules\Payroll\Models\SalaryStructure;

class SalaryStructurePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('salary-structures.view');
    }

    public function create(User $user): bool
    {
        return $user->can('salary-structures.create');
    }

    public function update(User $user, SalaryStructure $salaryStructure): bool
    {
        return $user->can('salary-structures.update');
    }
}
