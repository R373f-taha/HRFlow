<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;
use Modules\Employees\Models\Employee;
use Modules\Leave\Models\LeaveBalance;
use Modules\Leave\Models\LeaveType;
use Modules\Organization\Models\Department;
use Modules\Organization\Models\JobTitle;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

describe(" Leave Balances API tests ", function () {
    test('employee can retrieve their own leave balance when granted permission', function () {

        $user = User::factory()->create();
        $department=Department::factory()->create();
        $employee = Employee::factory()->create(['user_id' => $user->id,'department_id'=>$department->id
        
        ,'job_title_id'=>function (array $attributes) {
            return JobTitle::factory()->create([
                'department_id' => $attributes['department_id'],
            ])->id;
        },]);
        $leaveType=LeaveType::factory()->create();

        $permission = Permission::firstOrCreate(['name' => 'leave-requests.balance.view', 'guard_name' => 'api']);
        $user->givePermissionTo($permission);

        LeaveBalance::factory()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'year' => (int) date('Y'),
            'allocated_days' => 15,
            'used_days' => 3,
            'remaining_days' => 12,
        ]);

        actingAs($user, 'sanctum')
            ->getJson('/api/v1/leave-requests/balance')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'employee_id', 'leave_type', 'year', 'allocated_days', 'used_days', 'remaining_days'],
                ],
            ]);
    });

    test('hr_admin or manager can view any employee leave balance', function () {
        $hrAdmin = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'hr-admin', 'guard_name' => 'api']);
        $hrAdmin->assignRole($role);

        $employee = setupEmployeeContext()['employee'];
        $leaveType=LeaveType::factory()->create();
        LeaveBalance::factory()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'year' => (int) date('Y'),
        ]);

        actingAs($hrAdmin, 'sanctum')
            ->getJson("/api/v1/employees/{$employee->id}/leave-balance")
            ->assertStatus(200);
    });
});
