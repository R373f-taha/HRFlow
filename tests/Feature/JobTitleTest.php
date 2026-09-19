<?php

use App\Support\Cache\CacheSupport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Modules\Auth\Models\User;
use Modules\Organization\Actions\CreateJobTitleAction;
use Modules\Organization\Actions\DeleteJobTitleAction;
use Modules\Organization\Actions\UpdateJobTitleAction;
use Modules\Organization\Models\Department;
use Modules\Organization\Models\JobTitle;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

test('CreateJobTitleAction creates a job title and clears cached keys', function () {
    $department = Department::factory()->create();

    // Pre-populate cache keys to simulate existing cache state
    Cache::put('job_titles.all', ['dummy_data'], 3600);
    Cache::put("job_titles.department.{$department->id}", ['dummy_data'], 3600);

    expect(Cache::has('job_titles.all'))->toBeTrue()
        ->and(Cache::has("job_titles.department.{$department->id}"))->toBeTrue();

    // Execute Action
    $action = app(CreateJobTitleAction::class);
    $data = [
        'name' => 'Backend Developer',
        'department_id' => $department->id,
    ];

    $jobTitle = $action->execute($data);

    // Assert Database state
    assertDatabaseHas('job_titles', [
        'id' => $jobTitle->id,
        'name' => 'Backend Developer',
        'department_id' => $department->id,
    ]);

    // Assert Cache was invalidated cleanly
    expect(Cache::has('job_titles.all'))->toBeFalse()
        ->and(Cache::has("job_titles.department.{$department->id}"))->toBeFalse();
});

uses(RefreshDatabase::class);

test('UpdateJobTitleAction updates database and clears affected cache keys', function () {
    $department = Department::factory()->create();
    $jobTitle = JobTitle::factory()->create([
        'name' => 'Junior Developer',
        'department_id' => $department->id,
    ]);

    // Seed cache
    Cache::put('job_titles.all', ['cached_data'], 3600);
    Cache::put("job_titles.department.{$department->id}", ['cached_data'], 3600);

    // Execute Action
    $action = app(UpdateJobTitleAction::class);
    $action->execute($jobTitle, ['name' => 'Senior Developer']);

    // Assert Database state
       assertDatabaseHas('job_titles', [
        'id' => $jobTitle->id,
        'name' => 'Senior Developer',
    ]);

    // Assert Cache invalidation
    expect(Cache::has('job_titles.all'))->toBeFalse()
        ->and(Cache::has("job_titles.department.{$department->id}"))->toBeFalse();
});

test('UpdateJobTitleAction clears cache for both old and new departments when department changes', function () {
    $oldDepartment = Department::factory()->create();
    $newDepartment = Department::factory()->create();

    $jobTitle = JobTitle::factory()->create(['department_id' => $oldDepartment->id]);

    // Seed cache for both departments
    Cache::put("job_titles.department.{$oldDepartment->id}", ['cached_data'], 3600);
    Cache::put("job_titles.department.{$newDepartment->id}", ['cached_data'], 3600);

    // Reassign department
    $action = app(UpdateJobTitleAction::class);
    $action->execute($jobTitle, ['department_id' => $newDepartment->id]);

    // Both department cache keys must be cleared
    expect(Cache::has("job_titles.department.{$oldDepartment->id}"))->toBeFalse()
        ->and(Cache::has("job_titles.department.{$newDepartment->id}"))->toBeFalse();
});
test('DeleteJobTitleAction deletes model and purges cache entries', function () {
    $department = Department::factory()->create();
    $jobTitle = JobTitle::factory()->create(['department_id' => $department->id]);

    // Seed cache keys
    Cache::put('job_titles.all', ['cached_data'], 3600);
    Cache::put("job_titles.department.{$department->id}", ['cached_data'], 3600);

    // Execute Action
    $action = app(DeleteJobTitleAction::class);
    $result = $action->execute($jobTitle);

    // Assertions
    expect($result)->toBeTrue();
    assertDatabaseMissing('job_titles', ['id' => $jobTitle->id]);

    // Assert Cache was cleared
    expect(Cache::has('job_titles.all'))->toBeFalse()
        ->and(Cache::has("job_titles.department.{$department->id}"))->toBeFalse();
});
////////////////////////////////////////////////////////////////////////////



beforeEach(function () {
    // Seed or create hr-admin role if Spatie permissions are utilized
    Role::findOrCreate('hr-admin', 'api');
});

/*
|--------------------------------------------------------------------------
| AUTHORIZATION & AUTHENTICATION BOUNDARY TESTS
|--------------------------------------------------------------------------
*/

test('unauthenticated users cannot access any job title endpoints', function () {
    getJson('/api/v1/job-titles')->assertUnauthorized();
    postJson('/api/v1/job-titles', [])->assertUnauthorized();
    putJson('/api/v1/job-titles/1', [])->assertUnauthorized();
    deleteJson('/api/v1/job-titles/1')->assertUnauthorized();
});

test('regular authenticated users cannot create, update, or delete job titles', function () {
    $regularUser = User::factory()->create();
    Sanctum::actingAs($regularUser);

    $department = Department::factory()->create();
    $jobTitle = JobTitle::factory()->create(['department_id' => $department->id]);

    postJson('/api/v1/job-titles', ['name' => 'Test', 'department_id' => $department->id])->assertForbidden();
    putJson("/api/v1/job-titles/{$jobTitle->id}", ['name' => 'Test', 'department_id' => $department->id])->assertForbidden();
    deleteJson("/api/v1/job-titles/{$jobTitle->id}")->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| INDEX / READ TESTS (Authenticated User)
|--------------------------------------------------------------------------
*/

test('GET /api/v1/job-titles returns a list of cached job titles for authenticated users', function () {
    $regularUser = User::factory()->create();
    Sanctum::actingAs($regularUser);

    $department = Department::factory()->create(['name' => 'Engineering']);
    JobTitle::factory()->count(3)->create(['department_id' => $department->id]);

    $response = getJson('/api/v1/job-titles');

    $response->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'department_id', 'department' => ['id', 'name']],
            ],
        ]);

    expect(Cache::has('job_titles.all'))->toBeTrue();
});

test('GET /api/v1/job-titles?department_id={id} returns filtered job titles by department', function () {
    $regularUser = User::factory()->create();
    Sanctum::actingAs($regularUser);

    $departmentA = Department::factory()->create();
    $departmentB = Department::factory()->create();


    JobTitle::factory()->create([
        'department_id' => $departmentA->id,
        'name' => 'Machine Learning Engineer',
    ]);

    JobTitle::factory()->create([
        'department_id' => $departmentB->id,
        'name' => 'Deep Learning Engineer',
    ]);

    $response = getJson("/api/v1/job-titles/{$departmentA->id}");


    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.department_id', $departmentA->id);

    expect(Cache::has("job_titles.department.{$departmentA->id}"))->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| STORE / CREATE TESTS (hr-admin User Only)
|--------------------------------------------------------------------------
*/

test('POST /api/v1/job-titles creates a new job title and invalidates relevant cache keys', function () {
    $adminUser = User::factory()->create();
    $adminUser->assignRole('hr-admin');
    Sanctum::actingAs($adminUser);

    $department = Department::factory()->create();

    Cache::put('job_titles.all', ['cached_data'], 3600);
    Cache::put("job_titles.department.{$department->id}", ['cached_data'], 3600);

    $payload = [
        'name' => 'Backend Developer',
        'department_id' => $department->id,
    ];

    $response = postJson('/api/v1/job-titles', $payload);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Backend Developer')
        ->assertJsonPath('data.department_id', $department->id);

    assertDatabaseHas('job_titles', [
        'name' => 'Backend Developer',
        'department_id' => $department->id,
    ]);

    expect(Cache::has('job_titles.all'))->toBeFalse()
        ->and(Cache::has("job_titles.department.{$department->id}"))->toBeFalse();
});

test('POST /api/v1/job-titles fails validation when required fields are missing', function () {
    $adminUser = User::factory()->create();
    $adminUser->assignRole('hr-admin');
    Sanctum::actingAs($adminUser);

    $response = postJson('/api/v1/job-titles', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'department_id']);
});

test('POST /api/v1/job-titles fails validation when department_id does not exist', function () {
    $adminUser = User::factory()->create();
    $adminUser->assignRole('hr-admin');
    Sanctum::actingAs($adminUser);

    $response = postJson('/api/v1/job-titles', [
        'name' => 'DevOps Engineer',
        'department_id' => 9999,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['department_id']);
});



test('PUT /api/v1/job-titles/{id} updates name and purges cache entries', function () {
    $adminUser = User::factory()->create();
    $adminUser->assignRole('hr-admin');
    Sanctum::actingAs($adminUser);

    $department = Department::factory()->create();
    $jobTitle = JobTitle::factory()->create([
        'name' => 'Junior Developer',
        'department_id' => $department->id,
    ]);

    Cache::put('job_titles.all', ['cached_data'], 3600);
    Cache::put("job_titles.department.{$department->id}", ['cached_data'], 3600);

    $payload = [
        'name' => 'Senior Developer',
        'department_id' => $department->id,
    ];

    $response = putJson("/api/v1/job-titles/{$jobTitle->id}", $payload);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Senior Developer');

    assertDatabaseHas('job_titles', [
        'id' => $jobTitle->id,
        'name' => 'Senior Developer',
    ]);

    expect(Cache::has('job_titles.all'))->toBeFalse()
        ->and(Cache::has("job_titles.department.{$department->id}"))->toBeFalse();
});

test('PUT /api/v1/job-titles/{id} clears cache for both old and new department when reassigned', function () {
    $adminUser = User::factory()->create();
    $adminUser->assignRole('hr-admin');
    Sanctum::actingAs($adminUser);

    $oldDepartment = Department::factory()->create();
    $newDepartment = Department::factory()->create();

    $jobTitle = JobTitle::factory()->create(['department_id' => $oldDepartment->id]);

    Cache::put("job_titles.department.{$oldDepartment->id}", ['cached_data'], 3600);
    Cache::put("job_titles.department.{$newDepartment->id}", ['cached_data'], 3600);

    $response = putJson("/api/v1/job-titles/{$jobTitle->id}", [
        'name' => $jobTitle->name,
        'department_id' => $newDepartment->id,
    ]);

    $response->assertOk();

    expect(Cache::has("job_titles.department.{$oldDepartment->id}"))->toBeFalse()
        ->and(Cache::has("job_titles.department.{$newDepartment->id}"))->toBeFalse();
});

test('PUT /api/v1/job-titles/{id} returns 404 when resource is not found', function () {
    $adminUser = User::factory()->create();
    $adminUser->assignRole('hr-admin');
    Sanctum::actingAs($adminUser);

    $response = putJson('/api/v1/job-titles/9999', [
        'name' => 'Non Existent',
        'department_id' => 1,
    ]);

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| DELETE TESTS (hr-admin User Only)
|--------------------------------------------------------------------------
*/

test('DELETE /api/v1/job-titles/{id} deletes the record and invalidates cache', function () {
    $adminUser = User::factory()->create();
    $adminUser->assignRole('hr-admin');
    Sanctum::actingAs($adminUser);

    $department = Department::factory()->create();
    $jobTitle = JobTitle::factory()->create(['department_id' => $department->id]);

    Cache::put('job_titles.all', ['cached_data'], 3600);
    Cache::put("job_titles.department.{$department->id}", ['cached_data'], 3600);

    $response = deleteJson("/api/v1/job-titles/{$jobTitle->id}");

    $response->assertNoContent();

    assertDatabaseMissing('job_titles', ['id' => $jobTitle->id]);

    expect(Cache::has('job_titles.all'))->toBeFalse()
        ->and(Cache::has("job_titles.department.{$department->id}"))->toBeFalse();
});

test('DELETE /api/v1/job-titles/{id} returns 404 for missing resource', function () {
    $adminUser = User::factory()->create();
    $adminUser->assignRole('hr-admin');
    Sanctum::actingAs($adminUser);

    $response = deleteJson('/api/v1/job-titles/9999');

    $response->assertNotFound();
});
