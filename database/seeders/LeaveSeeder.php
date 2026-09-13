<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Employees\Models\Employee;
use Modules\Leave\Models\LeaveBalance;
use Modules\Leave\Models\LeaveRequest;
use Modules\Leave\Models\LeaveType;

class LeaveSeeder extends Seeder
{
    public function run(): void
    {
        $leaveTypes = collect([
            [
                'name' => 'Annual Leave',
                'annual_days' => 20,
                'is_paid' => true,
                'requires_document' => false,
            ],
            [
                'name' => 'Sick Leave',
                'annual_days' => 15,
                'is_paid' => true,
                'requires_document' => true,
            ],
            [
                'name' => 'Emergency Leave',
                'annual_days' => 5,
                'is_paid' => true,
                'requires_document' => false,
            ],
            [
                'name' => 'Unpaid Leave',
                'annual_days' => 30,
                'is_paid' => false,
                'requires_document' => false,
            ],
            [
                'name' => 'Maternity Leave',
                'annual_days' => 90,
                'is_paid' => true,
                'requires_document' => true,
            ],
        ])->map(
            fn (array $data) => LeaveType::create($data)  /*
             * Convert each array into an actual LeaveType model
             * and save it in the database.
             */
        );

        $employees = Employee::all();

        foreach ($employees as $employee) {
            foreach ($leaveTypes as $leaveType) {
                $allocated = $leaveType->annual_days;

                $used = fake()->randomFloat(
                    2,
                    0,
                    min($allocated, 10)
                );
                        /*
                 * Create the LeaveBalance.
                 *
                 * Here both foreign keys are explicitly provided.
                 *
                 * So LeaveBalanceFactory's:
                 *
                 * employee_id = null
                 * leave_type_id = null
                 *
                 * are replaced by real IDs.
                 */

                LeaveBalance::create([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'year' => now()->year,
                    'allocated_days' => $allocated,
                    'used_days' => $used,
                    'remaining_days' => round(
                        $allocated - $used,
                        2
                    ),
                ]);
            }
        }

        LeaveRequest::factory()
            ->count(1500)
            ->create([
                'employee_id' => fn () => $employees->random()->id,
                'leave_type_id' => fn () => $leaveTypes->random()->id,
            ]);
    }
}
