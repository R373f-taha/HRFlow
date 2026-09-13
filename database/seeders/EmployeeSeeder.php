<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Enums\UserRole;
use Modules\Auth\Models\User;
use Modules\Employees\Models\Employee;
use Modules\Employees\Models\EmployeeDocument;
use Modules\Organization\Models\Department;
use Modules\Organization\Models\JobTitle;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::with('jobTitles')->get();


         /**   we will store the Employees
         * that we create by empty collection because
         * We need this later when assigning Managers
         * and creating Documents.
         */

        $employees = collect();

        $count = (int) env('SEED_EMPLOYEES', 50);

        $this->command ?->info("Creating {$count} employees...");


        for ($i = 1; $i <= $count; $i++) {
            $department = $departments->random();

            $jobTitle = $department->jobTitles->random();// Pick a Job Title ONLY from that Department.


            $user = User::factory()
                ->employee()
                ->create([
                    'role' => UserRole::EMPLOYEE,
                ]);

            $employee = Employee::factory()->create([
                'user_id' => $user->id,
                'department_id' => $department->id,
                'job_title_id' => $jobTitle->id,
            ]);

            $employees->push($employee);// Save this Employee in our Collection.... We will need these employees later.


            if ($i % 100 === 0) {
                $this->command?->info(
                    "Created {$i} employees..."
                );
            }
        }


        /*
         * IMPORTANT:
         *
         * At this point Employees exist,
         * but Managers have not been assigned yet.
         *
         * So manager_id is still NULL for every employee.
         *
         * Now we can safely choose managers because
         * the Employees already exist.
         */

        $this->assignManagers($employees);

            /*
         * Now that Employees exist,
         * create their Documents.
         */
        $this->createDocuments($employees);
    }

    private function assignManagers($employees): void
    {

     /*
         * Group employees according to their Department.
         *
         * Example:
         *
         * Engineering:
         *   Employee 1
         *   Employee 5
         *   Employee 8
         */
        $departments = $employees->groupBy('department_id');// Group employees by department.

        foreach ($departments as $departmentEmployees) {


            /*
             * Choose one Employee from this Department
             * to become its Manager.
             */
            $manager = $departmentEmployees->random();

             /*
             * The Manager has no direct manager in this seed setup.
             *
             * Therefore:
             *
             * manager_id = NULL
             *
             * This NULL has a business meaning:
             *
             * "This employee is the top-level manager
             *  of this department."
             * ==> mean this manager doesn`t has manager in his department
             */
            $manager->update([
                'manager_id' => null,
            ]);
            /*
             * Change the related User's role from employee
             * to manager.
             */
            $manager->user->update([
                'role' => UserRole::MANAGER,
            ]);
               /*
             * Now assign this Manager to every other
             * employee in the same Department.
             */
            foreach ($departmentEmployees as $employee) {
                if ($employee->id === $manager->id) {
                    continue;
                }

                $employee->update([
                    'manager_id' => $manager->id,
                ]);
            }
             /*
                 * NOW manager_id gets a real FK.
                 *
                 * Example:
                 *
                 * manager.id = 7
                 *
                 * Employee:
                 * manager_id = 7
                 */

            $manager->department->update([
                'manager_id' => $manager->id,
            ]);
        }
    }

    private function createDocuments($employees): void
    {
        /*
         * Every Employee gets between 1 and 3 documents.
         */
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
