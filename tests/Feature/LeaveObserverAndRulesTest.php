<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Employees\Models\Employee;
use Modules\Leave\Models\LeaveRequest;
use Modules\Leave\Models\LeaveType;
use Modules\Leave\Rules\NoOverLappingLeave;

uses(RefreshDatabase::class);

describe(" Leave Observer & Validation Rules ", function () {

    test('observer automatically calculates inclusive days_count on creation', function () {
          ['user' => $user, 'employee' => $employee] = setupEmployeeContext();
          
         $leaveType = LeaveType::factory()->create(['requires_document' => false]);
        // 2026-10-01 to 2026-10-05 = 5 days inclusive
        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-05',
        ]);

        expect($leaveRequest->days_count)->toBe("5.00");
    });

    test('observer recalculates days_count when start or end dates are updated', function () {
          ['user' => $user, 'employee' => $employee] = setupEmployeeContext();
           $leaveType = LeaveType::factory()->create(['requires_document' => false]);
        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'leave_type_id' =>$leaveType->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-01', // 1 day
        ]);

        expect($leaveRequest->days_count)->toBe("1.00");

        $leaveRequest->update(['end_date' => '2026-10-03']); // Now 3 days
        expect($leaveRequest->days_count)->toBe("3.00");
    });

    test('NoOverLappingLeave rule detects overlapping date ranges', function () {
        ['user' => $user, 'employee' => $employee] = setupEmployeeContext();
         $leaveType = LeaveType::factory()->create(['requires_document' => false]);

        // Existing approved leave: Oct 10 to Oct 15
        LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-15',
            'status' => 'approved',
        ]);

        $failed = false;
        $failCallback = function ($message) use (&$failed) {
            $failed = true;
        };

        // Case A: Overlaps start
        $rule = new NoOverLappingLeave($employee->id, '2026-10-14', '2026-10-20');
        $rule->validate('start_date', '2026-10-14', $failCallback);

        expect($failed)->toBeTrue();
    });
});
