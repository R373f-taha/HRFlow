<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\Attendance\Models\Attendance;
use Modules\Employees\Models\Employee;

class AttendanceSeeder extends Seeder
{

   /*
         * We only want attendance records for ACTIVE employees.
         *
         * The employees already exist because EmployeeSeeder
         * runs before AttendanceSeeder.
         *
         * Important:
         * We are NOT using AttendanceFactory here.
         * We are creating the attendance rows manually because
         * we want to generate 30 records per employee efficiently.
         */
    public function run(): void
    {
        $employees = Employee::where('status', 'active')->get();

        $start = now()->subDays(30);

        foreach ($employees as $employee) {
            $rows = [];

            for ($day = 0; $day < 30; $day++) {
                $date = $start->copy()->addDays($day);

                $status = fake()->randomElement([
                    'present',
                    'present',
                    'present',
                    'late',
                    'absent',
                ]);

                $rows[] = [
                    'employee_id' => $employee->id,
                    'date' => $date->format('Y-m-d'),
                    'check_in' => $status === 'absent'    //absent employee doesn`t have any attendance record
                        ? null
                        : $date->copy()
                            ->setTime(
                                fake()->numberBetween(8, 9),
                                fake()->numberBetween(0, 59)
                            ),
                    'check_out' => $status === 'absent'
                        ? null
                        : $date->copy()
                            ->setTime(
                                fake()->numberBetween(16, 18),
                                fake()->numberBetween(0, 59)
                            ),
                    'status' => $status,
                    'note' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Attendance::insert($rows);
        }
    }
}
