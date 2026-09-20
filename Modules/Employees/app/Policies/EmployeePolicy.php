<?php

namespace Modules\Employees\Policies;

use Modules\Auth\Models\User;
use Modules\Employees\Models\Employee;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['hr-admin', 'manager']);
    }

    public function view(User $user, Employee $employee): bool
    {
        if ($user->hasRole('hr-admin')) {
            return true;
        }

        if ($user->hasRole('manager')) {
            return $user->employee?->id === $employee->manager_id;
        }

        return $user->employee?->id === $employee->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('hr-admin');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->hasRole('hr-admin');
    }

    public function terminate(User $user, Employee $employee): bool
    {
        return $user->hasRole('hr-admin');
    }

    public function manageDocuments(User $user, Employee $employee): bool
    {
        return $user->hasRole('hr-admin');
    }

    public function viewDocuments(User $user, Employee $employee): bool
    {
        if ($user->hasRole('hr-admin')) {
            return true;
        }

        if ($user->hasRole('manager')) {
            return $user->employee?->id === $employee->manager_id;
        }

        return $user->employee?->id === $employee->id;
    }
}
