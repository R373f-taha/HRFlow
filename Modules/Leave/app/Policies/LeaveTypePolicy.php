<?php

namespace Modules\Leave\Policies;

use Modules\Auth\Models\User;

class LeaveTypePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('hr-admin');
    }

    public function update(User $user): bool
    {
        return $user->hasRole('hr-admin');
    }
}
