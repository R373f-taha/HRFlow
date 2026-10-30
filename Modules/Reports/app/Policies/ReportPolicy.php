<?php

namespace Modules\Reports\Policies;

use Modules\Auth\Models\User;

class ReportPolicy
{
    /**
     * Determine if the user can view aggregate HR reports.
     */
    public function viewAny(User $user): bool
    {
        // Enforce HR Admin role requirement
        return $user->hasRole('hr-admin');
    }
}
