<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Auth\Models\User;
use Modules\Employees\Models\Employee;
use Modules\Organization\Models\Department;
use Modules\Organization\Models\JobTitle;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

describe("Employee Documents / policy permission tests", function () {

    /**
     * Helper to create a user assigned to a role.
     */
    function setupUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $roleModel = Role::firstOrCreate(['name' => $role, 'guard_name' => 'api']);
        $user->assignRole($roleModel);

        return $user;
    }

    /**
     * Helper to create an Employee record satisfying user_id, department_id, and job_title_id requirements.
     */
    function createEmployee(array $attributes = []): Employee
    {
        if (! isset($attributes['user_id'])) {
            $attributes['user_id'] = User::factory()->create()->id;
        }

        if (! isset($attributes['department_id'])) {
            $attributes['department_id'] = Department::factory()->create()->id;
        }

        if (! isset($attributes['job_title_id'])) {
            $attributes['job_title_id'] = JobTitle::factory()->create([
                'department_id' => $attributes['department_id'],
            ])->id;
        }

        return Employee::factory()->create($attributes);
    }

    test('unauthenticated users cannot access document endpoints', function () {
        $employee = createEmployee();

        getJson("/api/v1/employees/{$employee->id}/documents")
            ->assertStatus(401);

        postJson("/api/v1/employees/{$employee->id}/documents", [])
            ->assertStatus(401);
    });

    test('hr-admin can upload document for an employee', function () {
        Storage::fake('private');
        $admin = setupUserWithRole('hr-admin');
        $employee = createEmployee();

        $file = UploadedFile::fake()->create('contract.pdf', 200, 'application/pdf');

        actingAs($admin, 'sanctum')
            ->postJson("/api/v1/employees/{$employee->id}/documents", [
                'type' => 'Employment Contract',
                'document' => $file,
            ])
            ->assertStatus(201);

        assertDatabaseHas('employee_documents', [
            'employee_id' => $employee->id,
            'type' => 'Employment Contract',
            'original_name' => 'contract.pdf',
        ]);
    });

    test('non hr-admin employee cannot upload document', function () {
        Storage::fake('private');
        $regularUser = setupUserWithRole('employee');
        $employee = createEmployee();

        $file = UploadedFile::fake()->create('contract.pdf', 200, 'application/pdf');

        actingAs($regularUser, 'sanctum')
            ->postJson("/api/v1/employees/{$employee->id}/documents", [
                'type' => 'Employment Contract',
                'document' => $file,
            ])
            ->assertStatus(403);
    });

    test('employee can view their own documents', function () {
        $employeeUser = setupUserWithRole('employee');
        $employee = createEmployee(['user_id' => $employeeUser->id]);

        actingAs($employeeUser, 'sanctum')
            ->getJson("/api/v1/employees/{$employee->id}/documents")
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    });

    test('employee cannot view documents of another employee', function () {
        $employeeUser1 = setupUserWithRole('employee');
        $employeeUser2 = setupUserWithRole('employee');

        $employee1 = createEmployee(['user_id' => $employeeUser1->id]);
        $employee2 = createEmployee(['user_id' => $employeeUser2->id]);

        actingAs($employeeUser1, 'sanctum')
            ->getJson("/api/v1/employees/{$employee2->id}/documents")
            ->assertStatus(403);
    });

    test('manager can view documents of their direct report employee', function () {
        $managerUser = setupUserWithRole('manager');
        $managerEmployee = createEmployee(['user_id' => $managerUser->id]);

        $subordinateEmployee = createEmployee([
            'manager_id' => $managerEmployee->id,
        ]);

        actingAs($managerUser, 'sanctum')
            ->getJson("/api/v1/employees/{$subordinateEmployee->id}/documents")
            ->assertStatus(200);
    });

    test('document upload fails validation if required fields or file are missing', function () {
        Storage::fake('private');
        $admin = setupUserWithRole('hr-admin');
        $employee = createEmployee();

        actingAs($admin, 'sanctum')
            ->postJson("/api/v1/employees/{$employee->id}/documents", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type', 'document']);
    });
});
