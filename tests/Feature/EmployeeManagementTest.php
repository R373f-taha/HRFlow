<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Modules\Auth\Models\User;
use Modules\Employees\Events\EmployeeCreated;
use Modules\Employees\Models\Employee;
use Modules\Organization\Models\Department;
use Modules\Organization\Models\JobTitle;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;


uses(RefreshDatabase::class);

function setupEmployeeTestContext(string $role = 'hr-admin'): array
{
    $user = User::factory()->create();
    $roleModel = Role::firstOrCreate(['name' => $role, 'guard' => 'api']);
    $emp=Role::findOrCreate('employee', 'api');

    $permissions = [
        'employees.view',
        'employees.create',
        'employees.update',
        'employees.delete',
        'employees.terminate'
    ];

    foreach ($permissions as $perm) {
        $permissionModel = Permission::firstOrCreate(['name' => $perm, 'guard' => 'api']);
        $roleModel->givePermissionTo($permissionModel);
    }

    $user->assignRole($roleModel);

    $department = Department::factory()->create();
    $jobTitle = JobTitle::factory()->create(['department_id' => $department->id]);

    return [
        'user' => $user,
        'department' => $department,
        'jobTitle' => $jobTitle,
    ];
}

describe("Employee API / hr_admin tests", function () {

    test('unauthenticated users cannot access employee endpoints', function () {
        getJson('/api/v1/employees')
            ->assertStatus(401);
    });

    test('hr_admin can list employees with filters and pagination', function () {
        ['user' => $user, 'department' => $department, 'jobTitle' => $jobTitle] = setupEmployeeTestContext();

        Employee::factory()->count(3)->create([
            'user_id' => fn () => User::factory()->create()->id,
            'department_id' => $department->id,
            'job_title_id' => $jobTitle->id,
        ]);

        actingAs($user, 'sanctum')
            ->getJson('/api/v1/employees?page=1&per_page=10')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'employee_number', 'name', 'email', 'job_title', 'employment_type', 'hire_date', 'status']
                ],
                'links',
                'meta'
            ]);
    });

    test('hr_admin can create employee and triggers EmployeeCreated event', function () {

     //withoutExceptionHandling();
    Event::fake([EmployeeCreated::class]);
    Mail::fake();
         

        ['user' => $admin, 'department' => $department, 'jobTitle' => $jobTitle] = setupEmployeeTestContext();

        $payload = [
            'name' => 'Ahmad Taha',
            'email' => 'ahmad@example.com',
            'department_id' => $department->id,
            'job_title_id' => $jobTitle->id,
            'employment_type' => 'full_time',
            'hire_date' => '2026-01-01',
            'national_id' => 'NAT-99887766',
            'phone' => '+963911111111',
            'address' => 'Latakia, Syria',
        ];

        actingAs($admin, 'sanctum')
            ->postJson('/api/v1/employees', $payload)
            ->assertStatus(201)
            ->assertJsonPath('data.email', 'ahmad@example.com');

        assertDatabaseHas('users', [
            'name' => 'Ahmad Taha',
            'email' => 'ahmad@example.com',
            'is_active' => true,
        ]);

        assertDatabaseHas('employees', [
            'department_id' => $department->id,
            'national_id' => 'NAT-99887766',
            'status' => 'active',
        ]);

        Event::assertDispatched(EmployeeCreated::class, function ($event) {
            return $event->employee->user->email === 'ahmad@example.com'
                && !empty($event->temporaryPassword);
        });
    });

    test('employee creation fails validation when required fields are missing', function () {
        ['user' => $admin] = setupEmployeeTestContext();

        actingAs($admin, 'sanctum')
            ->postJson('/api/v1/employees', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'department_id', 'job_title_id', 'employment_type', 'hire_date', 'national_id']);
    });

    test('hr_admin can view single employee details', function () {
        ['user' => $admin, 'department' => $department, 'jobTitle' => $jobTitle] = setupEmployeeTestContext();

        $employeeUser = User::factory()->create();
        $employee = Employee::factory()->create([
            'user_id' => $employeeUser->id,
            'department_id' => $department->id,
            'job_title_id' => $jobTitle->id,
        ]);

        actingAs($admin, 'sanctum')
            ->getJson("/api/v1/employees/{$employee->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $employee->id);
    });

    test('hr_admin can update employee and user details', function () {
        ['user' => $admin, 'department' => $department, 'jobTitle' => $jobTitle] = setupEmployeeTestContext();

        $employeeUser = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);
        $employee = Employee::factory()->create([
            'user_id' => $employeeUser->id,
            'department_id' => $department->id,
            'job_title_id' => $jobTitle->id,
            'phone' => '+963000000',
        ]);

        $payload = [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'phone' => '+963999999',
        ];

        actingAs($admin, 'sanctum')
            ->putJson("/api/v1/employees/{$employee->id}", $payload)
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'New Name');

        assertDatabaseHas('users', [
            'id' => $employeeUser->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        assertDatabaseHas('employees', [
            'id' => $employee->id,
            'phone' => '+963999999',
        ]);
    });

    test('hr_admin can terminate an employee and deactivate user account', function () {
        ['user' => $admin, 'department' => $department, 'jobTitle' => $jobTitle] = setupEmployeeTestContext();

        $employeeUser = User::factory()->create(['is_active' => true]);
        $employee = Employee::factory()->create([
            'user_id' => $employeeUser->id,
            'department_id' => $department->id,
            'job_title_id' => $jobTitle->id,
            'status' => 'active',
        ]);

        $payload = [
            'termination_date' => '2026-09-20',
            'termination_reason' => 'End of Contract',
        ];

        actingAs($admin, 'sanctum')
            ->postJson("/api/v1/employees/{$employee->id}/terminate", $payload)
            ->assertStatus(200)
            ->assertJsonPath('message', 'Employee service terminated successfully.');

        assertDatabaseHas('employees', [
            'id' => $employee->id,
            'status' => 'terminated',
            'termination_date' => '2026-09-20 00:00:00',
            'termination_reason' => 'End of Contract',
        ]);

        assertDatabaseHas('users', [
            'id' => $employeeUser->id,
            'is_active' => false,
        ]);
    });

    test('hr_admin can view employee salary history', function () {
        ['user' => $admin, 'department' => $department, 'jobTitle' => $jobTitle] = setupEmployeeTestContext();

        $employee = Employee::factory()->create([
            'user_id' => User::factory()->create()->id,
            'department_id' => $department->id,
            'job_title_id' => $jobTitle->id,
        ]);

        actingAs($admin, 'sanctum')
            ->getJson("/api/v1/employees/{$employee->id}/salary-history")
            ->assertStatus(200);
    });
});

describe("Employee API / regular employee permissions tests", function () {

    function setupRegularUserContext(): array
    {
        $user = User::factory()->create();
        $roleModel = Role::firstOrCreate(['name' => 'employee', 'guard' => 'api']);
        $user->assignRole($roleModel);

        return ['user' => $user];
    }

    test('regular employee cannot create a new employee', function () {
        ['user' => $user] = setupRegularUserContext();

        actingAs($user, 'sanctum')
            ->postJson('/api/v1/employees', [])
            ->assertStatus(403);
    });

    test('regular employee cannot terminate an employee', function () {
        ['user' => $user] = setupRegularUserContext();

        $department = Department::factory()->create();
        $jobTitle = JobTitle::factory()->create(['department_id' => $department->id]);

        $employee = Employee::factory()->create([
            'user_id' => User::factory()->create()->id,
            'department_id' => $department->id,
            'job_title_id' => $jobTitle->id,
        ]);

        actingAs($user, 'sanctum')
            ->postJson("/api/v1/employees/{$employee->id}/terminate", [
                'termination_date' => '2026-09-20',
                'termination_reason' => 'Unauthorized',
            ])
            ->assertStatus(403);
    });
});
