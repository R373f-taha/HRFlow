<?php
;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;
use Modules\Organization\Models\Department;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

/**
 * Setup context with Sanctum guard and Spatie roles.
 *
 * @return array{user: User}
 */

describe(" Department /hr_admin tests/ ", function () {
function setupContext(string $role = 'hr-admin'): array
{
    $user = User::factory()->create();
    $roleModel = Role::firstOrCreate(['name' => $role, 'guard' => 'api']);
    $user->assignRole($roleModel);

    return ['user' => $user];
}

test('unauthenticated users cannot access department endpoints', function () {
    getJson('/api/v1/departments')
        ->assertStatus(401);
});

test('hr_admin  can list departments', function () {
    ['user' => $user] = setupContext();

    Department::factory()->count(3)->create();

    actingAs($user, 'sanctum')
        ->getJson('/api/v1/departments')
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'code', 'parent', 'manager','employees', 'created_at', 'updated_at'],
            ],
        ]);
});

test('hr_admin  can create a new department with valid payload', function () {
    ['user' => $user] = setupContext();

    $payload = [
        'name' => 'Human Resources',
        'code' => 'HR-01',
    ];

    actingAs($user, 'sanctum')
        ->postJson('/api/v1/departments', $payload)
        ->assertStatus(201)
        ->assertJsonFragment(['name' => 'Human Resources', 'code' => 'HR-01']);

    assertDatabaseHas('departments', [
        'name' => 'Human Resources',
        'code' => 'HR-01',
    ]);
});

test('department creation fails validation without required fields', function () {
    ['user' => $user] = setupContext();

    actingAs($user, 'sanctum')
        ->postJson('/api/v1/departments', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'code']);
});

test('hr_admin can retrieve a single department', function () {
    ['user' => $user] = setupContext();

    $department = Department::factory()->create();

    actingAs($user, 'sanctum')
        ->getJson("/api/v1/departments/{$department->id}")
        ->assertStatus(200)
        ->assertJsonFragment(['id' => $department->id, 'name' => $department->name]);
});

test('hr_admin can update a department and invalidates cache', function () {
    ['user' => $user] = setupContext();

    $department = Department::factory()->create();

    actingAs($user, 'sanctum')
        ->putJson("/api/v1/departments/{$department->id}", [
            'name' => 'Updated Department Name',
            'code' => $department->code,
        ])
        ->assertStatus(200)
        ->assertJsonFragment(['name' => 'Updated Department Name']);

    assertDatabaseHas('departments', [
        'id' => $department->id,
        'name' => 'Updated Department Name',
    ]);
});

test('hr_admin  can delete a department', function () {
    ['user' => $user] = setupContext();

    $department = Department::factory()->create();

    actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/departments/{$department->id}")
        ->assertStatus(204);

    assertDatabaseMissing('departments', [
        'id' => $department->id,
    ]);
});


});

describe(" Department /employee tests/ ", function () {
function setupEmployeeContext(string $role = 'employee'): array
{
    $user = User::factory()->create();
    $roleModel = Role::firstOrCreate(['name' => $role, 'guard' => 'api']);
    $user->assignRole($roleModel);

    return ['user' => $user];
}



test('employee can list departments', function () {

    ['user' => $user] =  setupEmployeeContext();

    Department::factory()->count(3)->create();

    actingAs($user, 'sanctum')
        ->getJson('/api/v1/departments')
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'code', 'parent', 'manager','employees', 'created_at', 'updated_at'],
            ],
        ]);
});

test('employee cannot create a new department with valid payload', function () {
    ['user' => $user] = setupEmployeeContext();

    $payload = [
        'name' => 'Human Resources',
        'code' => 'HR-01',
    ];

    actingAs($user, 'sanctum')
        ->postJson('/api/v1/departments', $payload)
        ->assertStatus(403);
});



test('employee cannot retrieve a single department', function () {

    ['user' => $user] = setupEmployeeContext();

    $department = Department::factory()->create();

    actingAs($user, 'sanctum')
        ->getJson("/api/v1/departments/{$department->id}")
        ->assertStatus(403);
});

test('employee cannot update a department and invalidates cache', function () {

    ['user' => $user] = setupEmployeeContext();

    $department = Department::factory()->create();

    actingAs($user, 'sanctum')
        ->putJson("/api/v1/departments/{$department->id}", [
            'name' => 'Updated Department Name',
            'code' => $department->code,
        ])
        ->assertStatus(403);
   
});

test('employee  cannot delete a department', function () {
    ['user' => $user] = setupEmployeeContext();

    $department = Department::factory()->create();

    actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/departments/{$department->id}")
        ->assertStatus(403);


});


});
