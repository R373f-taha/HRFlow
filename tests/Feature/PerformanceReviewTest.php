<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;
use Modules\Employees\Models\Employee;
use Modules\Organization\Models\Department;
use Modules\Organization\Models\JobTitle;
use Modules\Performance\Models\PerformanceCycle;
use Modules\Performance\Models\PerformanceReview;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

describe('Performance Review Feature Tests', function () {

    function setupPerformanceUser(string $role = 'employee', array $employeeAttrs = []): array
    {
        $user = User::factory()->create();
        $roleModel = Role::firstOrCreate(['name' => $role, 'guard_name' => 'api']);
        $user->assignRole($roleModel);

        $departmentId = $employeeAttrs['department_id'] ?? null;
        $department = $departmentId
            ? Department::find($departmentId)
            : Department::factory()->create();

        $jobTitle = JobTitle::factory()->create([
            'department_id' => $department->id,
            'name' => fake()->unique()->jobTitle,
        ]);

        $employeeData = array_merge([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'job_title_id' => $jobTitle->id,
        ], $employeeAttrs);

        $employee = Employee::factory()->create($employeeData);

        return [
            'user' => $user,
            'employee' => $employee,
            'department' => $department,
        ];
    }

    test('unauthenticated users cannot access performance review endpoints', function () {
        getJson('/api/v1/performance-reviews')->assertStatus(401);
        postJson('/api/v1/performance-reviews', [])->assertStatus(401);
        getJson('/api/v1/performance-reviews/my')->assertStatus(401);
        putJson('/api/v1/performance-reviews/1', [])->assertStatus(401);
    });

    test('manager can create performance review for an employee in their department', function () {
        ['user' => $managerUser, 'employee' => $manager, 'department' => $department] = setupPerformanceUser('employee', ['manager_id' => null]);

        $targetSetup = setupPerformanceUser('employee', [
            'department_id' => $department->id,
            'manager_id' => $manager->id,
        ]);
        $targetEmployee = $targetSetup['employee'];

        $cycle = PerformanceCycle::factory()->create(['status' => 'active']);

        $payload = [
            'performance_cycle_id' => $cycle->id,
            'employee_id' => $targetEmployee->id,
            'overall_rating' => 5,
            'strengths' => 'Exceptional code quality and architecture.',
            'areas_for_improvement' => 'Needs more focus on testing coverage.', // أطول من 8 أحرف لتتجاوز الـ Validation
            'goals' => 'Lead upcoming modules.',
        ];

        actingAs($managerUser, 'sanctum')
            ->postJson('/api/v1/performance-reviews', $payload)
            ->assertStatus(201)
            ->assertJson([
                'message' => 'Performance review submitted successfully.',
            ]);

        assertDatabaseHas('performance_reviews', [
            'performance_cycle_id' => $cycle->id,
            'employee_id' => $targetEmployee->id,
            'manager_id' => $manager->id,
            'overall_rating' => 5,
        ]);
    });

    test('returns 403 when creating review for a closed performance cycle', function () {
        ['user' => $managerUser, 'employee' => $manager, 'department' => $department] = setupPerformanceUser('employee', ['manager_id' => null]);

        $targetSetup = setupPerformanceUser('employee', [
            'department_id' => $department->id,
            'manager_id' => $manager->id,
        ]);
        $targetEmployee = $targetSetup['employee'];

        $closedCycle = PerformanceCycle::factory()->create(['status' => 'closed']);

        $payload = [
            'performance_cycle_id' => $closedCycle->id,
            'employee_id' => $targetEmployee->id,
            'overall_rating' => 4,
            'goals'=>'get promotion ',
            'strengths' => 'Good team collaboration and problem-solving skills.',
            'areas_for_improvement' => 'No improvements needed.',
        ];

        actingAs($managerUser, 'sanctum')
            ->postJson('/api/v1/performance-reviews', $payload)
            ->assertStatus(403)
            ->assertJson([
                'message' => 'You cannot create a performance review for a closed cycle.',
            ]);
    });

    test('employee can retrieve their own performance reviews', function () {
        ['user' => $employeeUser, 'employee' => $employee] = setupPerformanceUser('employee');

        $managerSetup = setupPerformanceUser('employee', ['manager_id' => null]);
        $manager = $managerSetup['employee'];

        $cycle = PerformanceCycle::factory()->create(['status' => 'active']);

        PerformanceReview::factory()->create([
            'performance_cycle_id' => $cycle->id,
            'employee_id' => $employee->id,
            'manager_id' => $manager->id,
        ]);

        actingAs($employeeUser, 'sanctum')
            ->getJson('/api/v1/performance-reviews/my')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
            ]);
    });

    test('manager can update their own performance review when cycle is active', function () {
        ['user' => $managerUser, 'employee' => $manager, 'department' => $department] = setupPerformanceUser('employee', ['manager_id' => null]);

        $targetSetup = setupPerformanceUser('employee', ['department_id' => $department->id]);
        $targetEmployee = $targetSetup['employee'];

        $cycle = PerformanceCycle::factory()->create(['status' => 'active']);

        $review = PerformanceReview::factory()->create([
            'performance_cycle_id' => $cycle->id,
            'employee_id' => $targetEmployee->id,
            'manager_id' => $manager->id,
            'overall_rating' => 3,
        ]);

        $updatePayload = [
            'overall_rating' => 5,
            'strengths' => 'Updated strengths after discussion.',
        ];

        actingAs($managerUser, 'sanctum')
            ->putJson("/api/v1/performance-reviews/{$review->id}", $updatePayload)
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Performance review updated successfully.',
            ]);

        assertDatabaseHas('performance_reviews', [
            'id' => $review->id,
            'overall_rating' => 5,
        ]);
    });

    test('authorized manager can view performance history of employee in their department', function () {
        ['user' => $managerUser, 'employee' => $manager, 'department' => $department] = setupPerformanceUser('employee', ['manager_id' => null]);

        $targetSetup = setupPerformanceUser('employee', ['department_id' => $department->id]);
        $targetEmployee = $targetSetup['employee'];

        $cycle = PerformanceCycle::factory()->create(['status' => 'active']);

        PerformanceReview::factory()->create([
            'performance_cycle_id' => $cycle->id,
            'employee_id' => $targetEmployee->id,
            'manager_id' => $manager->id,
        ]);

        actingAs($managerUser, 'sanctum')
            ->getJson("/api/v1/employees/{$targetEmployee->id}/performance")
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    });
});
