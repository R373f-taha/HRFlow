<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;
use Modules\Employees\Models\Employee;
use Modules\Organization\Models\Department;
use Modules\Organization\Models\JobTitle;
use Modules\Payroll\Models\PayrollRun;
use Modules\Payroll\Models\Payslip;
use Modules\Payroll\Models\SalaryStructure;
use Spatie\Permission\Models\Permission;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

/**
 * Context helper returning typed User and Employee models.
 *
 * @return array{user: User, employee: Employee}
 */
function setupPayrollEmployeeContext(): array
{
    /** @var User $user */
    $user = User::factory()->create();
    /** @var Department $department */
    $department = Department::factory()->create();

    /** @var Employee $employee */
    $employee = Employee::factory()->create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'job_title_id' => function (array $attributes) {
            return JobTitle::factory()->create([
                'department_id' => $attributes['department_id'],
            ])->id;
        },
    ]);

    return ['user' => $user, 'employee' => $employee];
}

describe(" Payroll API tests ", function () {

    test('authorized user can configure employee salary structure', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $permission = Permission::firstOrCreate(['name' => 'salary-structures.create', 'guard_name' => 'api']);
        $user->givePermissionTo($permission);

        $context = setupPayrollEmployeeContext();
        $employee = $context['employee'];

        $payload = [
            'employee_id' => $employee->id,
            'basic_salary' => 3500.00,
            'housing_allowance' => 500.00,
            'transport_allowance' => 200.00,
            'other_allowances' => 100.00,
            'effective_from' => '2026-01-01',
        ];

        actingAs($user, 'sanctum')
            ->postJson('/api/v1/salary-structures', $payload)
            ->assertStatus(201)
            ->assertJsonPath('data.basic_salary', "3500.00");

        assertDatabaseHas('salary_structures', [
            'employee_id' => $employee->id,
            'basic_salary' =>"3500.00",
        ]);
    });

    test('authorized user can create, process, and finalize payroll run', function () {
        /** @var User $user */
        $user = User::factory()->create();

        $permissions = [
            'payroll-runs.create',
            'payroll-runs.process',
            'payroll-runs.finalize',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'api']);
        }
        $user->givePermissionTo($permissions);

        $context = setupPayrollEmployeeContext();
        SalaryStructure::factory()->create([
            'employee_id' => $context['employee']->id,
            'basic_salary' => 2500.00,
        ]);

        // 1. Create Run
        $createRes = actingAs($user, 'sanctum')
            ->postJson('/api/v1/payroll-runs', [
                'year' => (int) date('Y'),
                'month' => 10,
            ]);

        $createRes->assertStatus(201);
        $runId = $createRes->json('data.id');

        // 2. Process Run
        actingAs($user, 'sanctum')
            ->postJson("/api/v1/payroll-runs/{$runId}/process")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'processed');

        // 3. Finalize Run
        actingAs($user, 'sanctum')
            ->postJson("/api/v1/payroll-runs/{$runId}/finalize")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'finalized');
    });

    test('returns 422 unprocessable entity when attempting to finalize a draft payroll run', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $permission = Permission::firstOrCreate(['name' => 'payroll-runs.finalize', 'guard_name' => 'api']);
        $user->givePermissionTo($permission);

        /** @var PayrollRun $payrollRun */
        $payrollRun = PayrollRun::factory()->create(['status' => 'draft']);

        actingAs($user, 'sanctum')
            ->postJson("/api/v1/payroll-runs/{$payrollRun->id}/finalize")
            ->assertStatus(422)
            ->assertJsonPath('message', 'Payroll run must be processed before it can be finalized.');
    });

    test('employee can retrieve their own payslips when granted permission', function () {
        $context = setupPayrollEmployeeContext();
        $user = $context['user'];
        $employee = $context['employee'];

        $permission = Permission::firstOrCreate(['name' => 'payslips.my.view', 'guard_name' => 'api']);
        $user->givePermissionTo($permission);

        /** @var PayrollRun $payrollRun */
        $payrollRun = PayrollRun::factory()->create(['status' => 'finalized']);
        Payslip::factory()->create([
            'payroll_run_id' => $payrollRun->id,
            'employee_id' => $employee->id,
            'basic_salary' => 3000.00,
            'net_salary' => 3000.00,
        ]);

        actingAs($user, 'sanctum')
            ->getJson('/api/v1/payslips/my')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'payroll_run_id', 'employee_id', 'basic_salary', 'net_salary'],
                ],
            ]);
    });

    test('authorized user can add deduction to a payslip', function () {
        /** @var User $user */
        $user = User::factory()->create();
        $permission = Permission::firstOrCreate(['name' => 'payslips.deductions.create', 'guard_name' => 'api']);
        $user->givePermissionTo($permission);
        $employeeContext = setupPayrollEmployeeContext();

        /** @var PayrollRun $payrollRun */
        $payrollRun = PayrollRun::factory()->create(['status' => 'processed']);
        /** @var Payslip $payslip */
        $payslip = Payslip::factory()->create([
            'payroll_run_id' => $payrollRun->id,
            'employee_id' => $employeeContext['employee']->id,
            'basic_salary' => 3000.00,
            'total_allowances' => 0.00,
            'total_deductions' => 0.00,
            'net_salary' => 3000.00,
        ]);

        actingAs($user, 'sanctum')
            ->postJson("/api/v1/payslips/{$payslip->id}/deductions", [
                'name' => 'Unpaid Absence',
                'amount' => 150.00,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.amount', 150);

        assertDatabaseHas('payslip_deductions', [
            'payslip_id' => $payslip->id,
            'name' => 'Unpaid Absence',
            'amount' => 150.00,
        ]);
    });
});
