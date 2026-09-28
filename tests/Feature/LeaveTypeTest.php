<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;
use Modules\Leave\Models\LeaveType;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

describe(" Leave Type / HR Admin endpoints ", function () {
    function setupHrAdminContext(): User
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'hr-admin', 'guard_name' => 'api']);
        $user->assignRole($role);

        return $user;
    }

    test('unauthenticated users cannot access leave type endpoints', function () {
        getJson('/api/v1/leave-types')->assertStatus(401);
    });

    test('hr_admin can list all leave types', function () {
        $user = setupHrAdminContext();
        LeaveType::factory()->count(3)->create();

        actingAs($user, 'sanctum')
            ->getJson('/api/v1/leave-types')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'annual_days', 'is_paid', 'requires_document', 'created_at'],
                ],
            ]);
    });

    test('hr_admin can create a new leave type with valid payload', function () {
        $user = setupHrAdminContext();

        $payload = [
            'name' => 'Annual Leave',
            'annual_days' => 20,
            'is_paid' => true,
            'requires_document' => false,
        ];

        actingAs($user, 'sanctum')
            ->postJson('/api/v1/leave-types', $payload)
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Annual Leave',
                'annual_days' => 20,
                'is_paid' => true,
                'requires_document' => false,
            ]);

        assertDatabaseHas('leave_types', ['name' => 'Annual Leave']);
    });

    test('creating leave type fails validation with missing or invalid fields', function () {
        $user = setupHrAdminContext();

        actingAs($user, 'sanctum')
            ->postJson('/api/v1/leave-types', [
                'name' => '',
                'annual_days' => 0, // Must be at least 1
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'annual_days']);
    });

    test('hr_admin can update an existing leave type', function () {
        $user = setupHrAdminContext();
        $leaveType = LeaveType::factory()->create(['name' => 'Old Name']);

        actingAs($user, 'sanctum')
            ->putJson("/api/v1/leave-types/{$leaveType->id}", [
                'name' => 'Sick Leave Updated',
                'annual_days' => 10,
                'is_paid' => true,
                'requires_document' => true,
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Sick Leave Updated']);

        assertDatabaseHas('leave_types', [
            'id' => $leaveType->id,
            'name' => 'Sick Leave Updated',
        ]);
    });
});

describe(" Leave Type / Employee authorization tests ", function () {
    function setupEmployeeUser(): User
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'api']);
        $user->assignRole($role);

        return $user;
    }

    test('employee can list leave types', function () {
        $user = setupEmployeeUser();
        LeaveType::factory()->count(2)->create();

        actingAs($user, 'sanctum')
            ->getJson('/api/v1/leave-types')
            ->assertStatus(200);
    });

    test('employee cannot create a leave type', function () {
        $user = setupEmployeeUser();

        actingAs($user, 'sanctum')
            ->postJson('/api/v1/leave-types', [
                'name' => 'Maternity Leave',
                'annual_days' => 90,
                'is_paid' => true,
                'requires_document' => true,
            ])
            ->assertStatus(403);
    });

    test('employee cannot update a leave type', function () {
        $user = setupEmployeeUser();
        $leaveType = LeaveType::factory()->create();

        actingAs($user, 'sanctum')
            ->putJson("/api/v1/leave-types/{$leaveType->id}", [
                'name' => 'Unauthorized Edit',
                'annual_days' => 15,
                'is_paid' => true,
                'requires_document' => false,
            ])
            ->assertStatus(403);
    });
});
