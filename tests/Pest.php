<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Modules\Auth\Models\User;
use Modules\Employees\Models\Employee;
use Modules\Organization\Models\Department;
use Modules\Organization\Models\JobTitle;
use Spatie\Permission\Models\Role;

pest()
    ->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/**
 * Setup standard employee context for API testing.
 */
function setupEmployeeContext(string $role = 'employee'): array
{
    $user = User::factory()->create();
    $department = Department::factory()->create();
  //  $jobTitle = \Modules\Organization\Models\JobTitle::factory()->create();

    $employee = Employee::factory()->create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'job_title_id' => function (array $attributes) {
            return JobTitle::factory()->create([
                'department_id' => $attributes['department_id'],
            ])->id;
        },
    ]);

    $roleModel = Role::firstOrCreate(['name' => $role, 'guard_name' => 'api']);
    $user->assignRole($roleModel);

    $user->refresh();

    return [
        'user' => $user,
        'employee' => $employee,
        'department' => $department,
    ];
}
