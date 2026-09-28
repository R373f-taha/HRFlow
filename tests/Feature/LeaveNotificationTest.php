<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Modules\Auth\Models\User;
use Modules\Employees\Models\Employee;
use Modules\Leave\Models\LeaveRequest;
use Modules\Leave\Models\LeaveType;
use Modules\Leave\Notifications\LeaveApprovedNotification;
use Modules\Leave\Notifications\PendingLeaveApprovalNotification;

uses(RefreshDatabase::class);



describe(" Leave Module Notifications ", function () {

    test('LeaveApprovedNotification formats email and database representation correctly', function () {
        $user = User::factory()->create(['name' => 'John Doe']);
             ['employee' => $employee] = setupEmployeeContext();
           $leaveType = LeaveType::factory()->create(['requires_document' => false]);

        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'days_count' => 3,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-03',
        ]);

        $notification = new LeaveApprovedNotification($leaveRequest);

        // Test Mail format
        $mail = $notification->toMail($employee->user);
        expect($mail->subject)->toContain("Your Leave Request Has Been Approved - #{$leaveRequest->id}");

        // Test Array format for DB channel
        $arrayData = $notification->toArray($user);
        expect($arrayData['leave_request_id'])->toBe($leaveRequest->id)
            ->and($arrayData['days_count'])->toBe("3.00")
            ->and($arrayData['status'])->toBe('approved');
    });

    test('PendingLeaveApprovalNotification formats email and array data correctly', function () {

        $manager = User::factory()->create(['name' => 'Manager Name']);
         ['user' => $user, 'employee' => $employee] = setupEmployeeContext();
           $leaveType = LeaveType::factory()->create(['requires_document' => false]);
        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'days_count' => 2,
        ]);

        $notification = new PendingLeaveApprovalNotification($leaveRequest);

        $mail = $notification->toMail($manager);
        expect($mail->subject)->toContain("New Leave Request Pending Approval - #{$leaveRequest->id}");

        $arrayData = $notification->toArray($manager);
        expect($arrayData['leave_request_id'])->toBe($leaveRequest->id)
            ->and($arrayData['employee_id'])->toBe($employee->id);
    });
});
