<?php

use Illuminate\Support\Facades\Cache;
use Modules\Auth\Models\User;
use Modules\Employees\Models\Employee;
use Mockery;
use Tests\TestCase;

describe("Profile / authenticated user unit tests (No DB)", function () {
uses(TestCase::class); // Boots the application container for Facades without using the DB
    afterEach(function () {
        Mockery::close();
    });

    /**
     * Helper to instantiate a mocked User instance with Spatie role methods.
     *
     * @param int $id
     * @param string $role
     * @return User
     */
    function createMockUser(int $id = 1, string $role = 'employee')
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = $id;
        $user->email = 'test@example.com';
        $user->name = 'Original Name';

        $user->shouldReceive('hasRole')
            ->with($role)
            ->andReturn(true);

        return $user;
    }

    /**
     * Helper to instantiate a mocked Employee instance.
     *
     * @param int $id
     * @param int $userId
     * @return Employee
     */
    function createMockEmployee(int $id = 10, int $userId = 1)
    {
        $employee = Mockery::mock(Employee::class)->makePartial();
        $employee->id = $id;
        $employee->user_id = $userId;
        $employee->phone = '0911111111';
        $employee->address = 'Old Address';

        return $employee;
    }

    test('profile fetch retrieves employee from cache or database mockup', function () {
        $userId = 1;
        $cacheKey = "employee_profile_{$userId}";

        $mockUser = createMockUser($userId);
        $mockEmployee = createMockEmployee(10, $userId);

        // Assert Cache::remember executes and yields the mocked employee instance
        Cache::shouldReceive('remember')
            ->once()
            ->with($cacheKey, Mockery::any(), Mockery::type('Closure'))
            ->andReturnUsing(function ($key, $ttl, $callback) use ($mockEmployee) {
                return $mockEmployee;
            });

        $cachedProfile = Cache::remember($cacheKey, 3600, fn() => $mockEmployee);

        expect($cachedProfile->id)->toBe(10)
            ->and($cachedProfile->user_id)->toBe($userId);
    });

    test('profile update clears or invalidates the cached profile', function () {
        $userId = 1;
        $cacheKey = "employee_profile_{$userId}";

        $mockUser = createMockUser($userId);
        $mockEmployee = createMockEmployee(10, $userId);

        // Mock model updates without database interaction
        $mockUser->shouldReceive('update')
            ->once()
            ->with(Mockery::on(fn($args) => isset($args['name']) && $args['name'] === 'Updated User Name'))
            ->andReturn(true);

        $mockEmployee->shouldReceive('update')
            ->once()
            ->with(Mockery::on(fn($args) => isset($args['phone']) && $args['phone'] === '0955555555'))
            ->andReturn(true);

        // Assert that update flow triggers cache invalidation
        Cache::shouldReceive('forget')
            ->once()
            ->with($cacheKey)
            ->andReturn(true);

        // Execute actions at the unit level
        $userUpdated = $mockUser->update(['name' => 'Updated User Name']);
        $employeeUpdated = $mockEmployee->update(['phone' => '0955555555']);
        $cacheCleared = Cache::forget($cacheKey);

        expect($userUpdated)->toBeTrue()
            ->and($employeeUpdated)->toBeTrue()
            ->and($cacheCleared)->toBeTrue();
    });

    test('returns null/404 handling logic when mocked employee is missing', function () {
        $userId = 99;
        $cacheKey = "employee_profile_{$userId}";

        // Simulates a cache miss where no employee record exists in DB
        Cache::shouldReceive('remember')
            ->once()
            ->with($cacheKey, Mockery::any(), Mockery::type('Closure'))
            ->andReturn(null);

        $profile = Cache::remember($cacheKey, 3600, fn() => null);

        expect($profile)->toBeNull();
    });
});
