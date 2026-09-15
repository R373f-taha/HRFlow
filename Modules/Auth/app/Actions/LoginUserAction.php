<?php

namespace Modules\Auth\Actions;

use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\User;
use RuntimeException;

class LoginUserAction
{
    /**
     * Authenticate a user and create a Sanctum access token.
     *
     * @return array{user: User, token: string}
     *
     * @throws RuntimeException
     */
    public function execute(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new RuntimeException('Invalid credentials.');
        }

        if (! $user->is_active) {
            throw new RuntimeException('Your account is inactive.');
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
