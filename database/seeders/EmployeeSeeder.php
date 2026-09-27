<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Enums\UserRole;
use Modules\Auth\Models\User;
use Modules\Employees\Models\Employee;
use Modules\Employees\Models\EmployeeDocument;
use Modules\Organization\Models\Department;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::with('jobTitles')->get();
        $employees = collect();

        $count = (int) env('SEED_EMPLOYEES', 50);

        $this->command?->info("Creating {$count} employees...");

        for ($i = 1; $i <= $count; $i++) {
            $department = $departments->random();
            $jobTitle = $department->jobTitles->random();

            $user = User::factory()
                ->employee()
                ->create([
                    'role' => UserRole::EMPLOYEE,
                ]);

            // Assign Spatie Role to write record in model_has_roles table
            $user->assignRole('employee');

            $employee = Employee::factory()->create([
                'user_id' => $user->id,
                'department_id' => $department->id,
                'job_title_id' => $jobTitle->id,
            ]);

            $employees->push($employee);

            if ($i % 100 === 0) {
                $this->command?->info("Created {$i} employees...");
            }
        }

        $this->assignManagers($employees);
        $this->createDocuments($employees);
    }

    private function assignManagers($employees): void
    {
        $departments = $employees->groupBy('department_id');

        foreach ($departments as $departmentEmployees) {
            $manager = $departmentEmployees->random();

            $manager->update([
                'manager_id' => null,
            ]);

            // 1. Update DB enum column
            $manager->user->update([
                'role' => UserRole::MANAGER,
            ]);

            // 2. Sync Spatie role (replaces 'employee' with 'department-manager')
            $manager->user->syncRoles(['department-manager']);

            foreach ($departmentEmployees as $employee) {
                if ($employee->id === $manager->id) {
                    continue;
                }

                $employee->update([
                    'manager_id' => $manager->id,
                ]);
            }

            $manager->department->update([
                'manager_id' => $manager->id,
            ]);
        }
    }

    private function createDocuments($employees): void
    {
        foreach ($employees as $employee) {
            $count = fake()->numberBetween(1, 3);

            EmployeeDocument::factory()
                ->count($count)
                ->create([
                    'employee_id' => $employee->id,
                ]);
        }
    }
}
