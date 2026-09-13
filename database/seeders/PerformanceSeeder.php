<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Employees\Models\Employee;
use Modules\Performance\Models\PerformanceCycle;
use Modules\Performance\Models\PerformanceReview;

class PerformanceSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::whereNotNull('manager_id')->get();


        /*
         * We only select Employees who already have a Manager.
         *
         * Why?
         *
         * Because a Performance Review requires:
         *
         * employee
         * manager
         * performance cycle
         *
         * Therefore employees with manager_id = NULL
         * cannot be used here.
         */

        $cycles = collect();

        /*
         * Create 3 Performance Cycles.
         */
        for ($i = 1; $i <= 3; $i++) {
            $cycles->push(
                PerformanceCycle::create([
                    'name' => "Performance Cycle {$i}",
                    'starts_at' => now()
                        ->subMonths(12 - ($i * 3))
                        ->startOfMonth()
                        ->format('Y-m-d'),
                    'ends_at' => now()
                        ->subMonths(9 - ($i * 3))
                        ->endOfMonth()
                        ->format('Y-m-d'),
                    'status' => 'closed',
                ])
            );
        }

        foreach ($cycles as $cycle) {

            /*
             * ...create a Review for every employee
             * who has a manager.
             */
            foreach ($employees as $employee) {
                PerformanceReview::create([
                    'performance_cycle_id' => $cycle->id,
                    'employee_id' => $employee->id,
                    'manager_id' => $employee->manager_id,
                    'overall_rating' => fake()->numberBetween(1, 5),
                    'strengths' => fake()->paragraph(),
                    'areas_for_improvement' => fake()->paragraph(),
                    'goals' => fake()->paragraph(),
                ]);
            }
        }
    }
}
