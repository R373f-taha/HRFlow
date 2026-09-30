<?php

namespace Modules\Attendance\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Attendance\Models\Attendance;
use Modules\Auth\Models\User;

class AttendancePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any attendance records.
     */
    public function viewAny(User $user): bool
   { // {dd([
    //     'user_id' => $user->id,
    //     'roles' => $user->getRoleNames(),
    //     'has_role_default' => $user->hasRole('hr-admin'),
    //     'has_role_sanctum' => $user->hasRole('hr-admin', 'sanctum'),
    // ]);
        return $user->hasAnyRole(['hr-admin', 'manager']);
    }

    /**
     * Determine whether the user can view the specific attendance record.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        // HR Admin can view all
        if ($user->hasRole('hr-admin')) {
            return true;
        }

        // Employee can view their own record
        if ($user->employee && $user->employee->id === $attendance->employee_id) {
            return true;
        }

        // Department Manager can view their department employees' attendance
        if ($user->hasRole('manager') && $user->employee) {
            return $user->employee->department_id === $attendance->employee?->department_id;
        }

        return false;
    }

    /**
     * Determine whether the user can record attendance (single or bulk).
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['hr-admin', 'manager']);
    }

    /**
     * Determine whether the user can correct/update an attendance record.
     */
    public function update(User $user, Attendance $attendance): bool
    {
        return $user->hasRole('hr-admin');
    }

    /**
     * Determine whether the user can delete an attendance record.
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        return $user->hasRole('hr-admin');
    }

    /**
     * Determine whether the user can view monthly department summaries.
     */
    public function viewSummary(User $user): bool
    {
        return $user->hasAnyRole(['hr-admin', 'manager']);
    }
}
