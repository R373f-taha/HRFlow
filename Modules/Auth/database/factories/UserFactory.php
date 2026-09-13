<?php

namespace Modules\Auth\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Enums\UserRole;
use Modules\Auth\Models\User;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),

            'email' => fake()->unique()->safeEmail(),

            'email_verified_at' => now(),

            'password' => Hash::make('password'),

            'role' => UserRole::EMPLOYEE,

            'is_active' => true,

            'remember_token' => null,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::ADMIN,
        ]);
    }

    public function manager(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::MANAGER,
        ]);
    }

    public function employee(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::EMPLOYEE,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}
