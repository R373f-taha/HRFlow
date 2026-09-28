<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;
use Modules\Employees\Models\Employee;
use Modules\Leave\Models\LeaveBalance;
use Modules\Leave\Models\LeaveRequest;
use Modules\Leave\Models\LeaveType;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

describe(" Leave Request Approval & Rejection / HR Admin & Manager ", function () {

    function setupManagerUser(): User
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'api']);
        $user->assignRole($role);

        return $user;
    }

    test('manager can approve pending leave request and update employee balance', function () {
        $manager = setupManagerUser();
        $context = setupEmployeeContext();
        $employee = $context['employee'];
        $leaveType = LeaveType::factory()->create();

        // Establish leave balance for employee (20 allocated, 0 used)
        LeaveBalance::factory()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'year' => (int) date('Y'),
            'allocated_days' => 20,
            'used_days' => 0,
            'remaining_days' => 20,
        ]);

        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-05',
            'days_count' => 5,
            'status' => 'pending',
        ]);

        actingAs($manager, 'sanctum')
            ->patchJson("/api/v1/leave-requests/{$leaveRequest->id}/approve")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'approved');

        assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'approved',
        ]);

        assertDatabaseHas('leave_balances', [
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'used_days' => 5,
            'remaining_days' => 15,
        ]);
    });

    test('manager can reject pending leave request with a rejection reason', function () {
        $manager = setupManagerUser();
        $context = setupEmployeeContext();
        $leaveType = LeaveType::factory()->create();

        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $context['employee']->id,
            'leave_type_id' => $leaveType->id,
            'status' => 'pending',
        ]);

        actingAs($manager, 'sanctum')
            ->patchJson("/api/v1/leave-requests/{$leaveRequest->id}/reject", [
                'rejection_reason' => 'High workload during project release period.',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'rejected');

        assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'rejected',
            'rejection_reason' => 'High workload during project release period.',
        ]);
    });

    test('approval fails if employee has insufficient remaining leave days balance', function () {
        $manager = setupManagerUser();
        $context = setupEmployeeContext();
        $employee = $context['employee'];
        $leaveType = LeaveType::factory()->create();

        LeaveBalance::factory()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'year' => (int) date('Y'),
            'allocated_days' => 10,
            'used_days' => 8,
            'remaining_days' => 2, // Only 2 days left
        ]);

        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'days_count' => 5, // Requires 5 days
            'status' => 'pending',
        ]);

        actingAs($manager, 'sanctum')
            ->patchJson("/api/v1/leave-requests/{$leaveRequest->id}/approve")
            ->assertStatus(422);
    });

});
