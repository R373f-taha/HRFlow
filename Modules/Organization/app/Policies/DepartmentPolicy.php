<?php

namespace Modules\Organization\Policies;

use Modules\Auth\Models\User;
use Modules\Organization\Models\Department;

class DepartmentPolicy
{
    /**
     * Determine whether the user can view the department.
     *
     * HR Admin:
     * - Can view any department.
     *
     * Department Manager:
     * - Can view departments.
     * - The actual department scope is based on the manager's
     *   relationship to the department.
     */
   /**
     * Determine whether the user can view any departments list.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['hr-admin', 'department-manager', 'employee']);
    }

    /**
     * Determine whether the user can view a specific department.
     */
    public function view(User $user, Department $department): bool
    {
        if ($user->hasRole('hr-admin')) {
            return true;
        }

        if ($user->hasRole('department-manager')) {
            return $department->manager_id === $user->employee?->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create departments.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('hr-admin');
    }

    /**
     * Determine whether the user can update the department.
     */
    public function update(User $user, Department $department): bool
    {
        return $user->hasRole('hr-admin');
    }

    /**
     * Determine whether the user can delete the department.
     */
    public function delete(User $user, Department $department): bool
    {
        return $user->hasRole('hr-admin');
    }
}
