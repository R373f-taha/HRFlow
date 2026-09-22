<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;
use Modules\Employees\Models\Employee;
use Modules\Organization\Models\Department;
use Modules\Organization\Models\JobTitle;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

describe("Profile / authenticated user tests", function () {

    function setupProfileContext(string $role = 'employee'): array
    {
        $user = User::factory()->create();
        $roleModel = Role::firstOrCreate(['name' => $role, 'guard_name' => 'api']);
        $user->assignRole($roleModel);

        return ['user' => $user];
    }

    /**
     * Helper to create an Employee with necessary foreign keys.
     */
    function createProfileEmployee(array $attributes = []): Employee
    {
        $department = Department::factory()->create();
        $jobTitle = JobTitle::factory()->create(['department_id' => $department->id]);

        return Employee::factory()->create(array_merge([
            'department_id' => $department->id,
            'job_title_id'  => $jobTitle->id,
        ], $attributes));
    }

    test('unauthenticated users cannot access profile endpoints', function () {
        getJson('/api/v1/employees/me')
            ->assertStatus(401);

        putJson('/api/v1/employees/me', [])
            ->assertStatus(401);
    });

    test('authenticated user can view their profile', function () {
        ['user' => $user] = setupProfileContext();
        createProfileEmployee(['user_id' => $user->id]);

        actingAs($user, 'sanctum')
            ->getJson('/api/v1/employees/me')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
            ]);
    });

    test('returns 404 if authenticated user has no associated employee record', function () {
        ['user' => $user] = setupProfileContext();

        actingAs($user, 'sanctum')
            ->getJson('/api/v1/employees/me')
            ->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Employee profile not found for this user.',
            ]);
    });

    test('authenticated user can update their profile information', function () {
        ['user' => $user] = setupProfileContext();
        $employee = createProfileEmployee(['user_id' => $user->id]);

        $payload = [
            'name' => 'Updated User Name',
            'email' => $user->email,
            'phone' => '0955555555',
            'address' => 'Updated Address Here',
        ];

        actingAs($user, 'sanctum')
            ->putJson('/api/v1/employees/me', $payload)
            ->assertStatus(200);

        assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User Name',
        ]);

        assertDatabaseHas('employees', [
            'id' => $employee->id,
            'phone' => '0955555555',
            'address' => 'Updated Address Here',
        ]);
    });

    test('profile update validation fails with invalid email format', function () {
        ['user' => $user] = setupProfileContext();
        createProfileEmployee(['user_id' => $user->id]);

        actingAs($user, 'sanctum')
            ->putJson('/api/v1/employees/me', [
                'email' => 'not-an-email',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });
});
