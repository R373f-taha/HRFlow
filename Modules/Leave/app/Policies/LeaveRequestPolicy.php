<?php

namespace Modules\Leave\Policies;

use Modules\Auth\Models\User;
use Modules\Leave\Models\LeaveRequest;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('employee');
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->hasAnyRole(['hr-admin', 'manager'])) {
            return true;
        }

        return $user->employee?->id === $leaveRequest->employee_id;
    }

    public function approve(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->hasAnyRole(['hr-admin', 'manager']);
    }

    public function reject(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->hasAnyRole(['hr-admin', 'manager']);
    }

    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->employee?->id === $leaveRequest->employee_id
            && $leaveRequest->status === 'pending';
    }

    public function viewBalance(User $user): bool
    {
        // return $user->employee !== null;
        //   return $user->hasRole('employee','api');
        // Checks permission 'leave-requests.balance.view' regardless of role name
        // return $user->hasPermissionTo('leave-requests.balance.view', 'api');
        return $user->can('leave-requests.balance.view');
    }

    public function viewEmployeeBalance(User $user): bool
    {
        return $user->hasAnyRole(['hr-admin', 'manager']);
    }
}

