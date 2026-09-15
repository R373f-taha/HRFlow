<?php

namespace Modules\Auth\Actions;

use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\User;
use RuntimeException;

class ChangePasswordAction
{
    /**
     * Change the authenticated user's password.
     */
    public function execute(User $user, string $currentPassword,string $newPassword ): void {
        
        if (! Hash::check($currentPassword, $user->password)) {
            throw new RuntimeException('The current password is incorrect.');
        }

        $user->update([
            'password' => $newPassword,
        ]);
    }
}
