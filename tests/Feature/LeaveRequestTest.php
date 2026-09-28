<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Leave\Models\LeaveRequest;
use Modules\Leave\Models\LeaveType;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

describe(" Leave Requests / Employee workflows ", function () {

    test('employee can submit a valid leave request without document if not required', function () {
        ['user' => $user, 'employee' => $employee] = setupEmployeeContext();
        $leaveType = LeaveType::factory()->create(['requires_document' => false]);

        $payload = [
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-02',
            'reason' => 'Short trip',
        ];

        actingAs($user, 'sanctum')
            ->postJson('/api/v1/leave-requests', $payload)
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'pending');

        assertDatabaseHas('leave_requests', [
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'days_count' => 2,
        ]);
    });

    test('leave request fails when medical document is required for requests over 3 days', function () {
        ['user' => $user] = setupEmployeeContext();
        $leaveType = LeaveType::factory()->create(['requires_document' => true]);

        $payload = [
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-05',
            'reason' => 'Sick leave',
        ];

        actingAs($user, 'sanctum')
            ->postJson('/api/v1/leave-requests', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['document']);
    });

    test('employee can upload a document when required for leave > 3 days', function () {

        Storage::fake('public');
        ['user' => $user] = setupEmployeeContext();
        $leaveType = LeaveType::factory()->create(['requires_document' => true]);

        $payload = [
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-05',
            'reason' => 'Medical recovery',
            'document' => UploadedFile::fake()->create('medical_report.pdf', 500, 'application/pdf'),
        ];

        actingAs($user, 'sanctum')
            ->postJson('/api/v1/leave-requests', $payload)
            ->assertStatus(201);
    });

 test('employee can delete their own pending leave request', function () {
    ['user' => $user, 'employee' => $employee] = setupEmployeeContext();
    $leaveType = LeaveType::factory()->create();

    $leaveRequest = LeaveRequest::factory()->create([
        'employee_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'status' => 'pending',
    ]);

    actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/leave-requests/{$leaveRequest->id}")
        ->assertStatus(204);

    assertDatabaseMissing('leave_requests', ['id' => $leaveRequest->id]);
});

test('employee cannot delete an approved leave request', function () {
    ['user' => $user, 'employee' => $employee] = setupEmployeeContext();
    $leaveType = LeaveType::factory()->create();

    $leaveRequest = LeaveRequest::factory()->create([
        'employee_id' => $employee->id,
        'leave_type_id' => $leaveType->id, 
        'status' => 'approved',
    ]);

    actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/leave-requests/{$leaveRequest->id}")
        ->assertStatus(403);
});
});
