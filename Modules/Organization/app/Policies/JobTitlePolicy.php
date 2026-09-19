<?php

namespace Modules\Organization\Policies;

use Modules\Auth\Models\User;
use Modules\Organization\Models\JobTitle;

class JobTitlePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('hr-admin');
    }

    public function update(User $user, JobTitle $jobTitle): bool
    {
        return $user->hasRole('hr-admin');
    }

    public function delete(User $user, JobTitle $jobTitle): bool
    {
       return $user->hasRole('hr-admin'); // Return true if the user has the 'hr-admin' role
    }
}
