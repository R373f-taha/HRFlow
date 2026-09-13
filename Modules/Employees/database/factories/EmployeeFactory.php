<?php

namespace Modules\Employees\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Employees\Models\Employee;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'user_id' => null,

            'employee_number' => 'EMP-' . fake()->unique()->numerify('######'),

            'department_id' => null,

            'job_title_id' => null,

            'manager_id' => null,

            'employment_type' => fake()->randomElement([
                'full_time',
                'part_time',
                'contract',
            ]),

            'hire_date' => fake()->dateTimeBetween(
                '-8 years',
                '-1 month'
            )->format('Y-m-d'),

            'termination_date' => null,

            'termination_reason' => null,

            'status' => 'active',

            'national_id' => fake()->unique()->numerify('############'),

            'phone' => fake()->phoneNumber(),

            'address' => fake()->address(),
        ];
    }

    public function terminated(): static
    {
        return $this->state(fn () => [
            'status' => 'terminated',

            'termination_date' => fake()->dateTimeBetween(
                '-1 year',
                '-1 month'
            )->format('Y-m-d'),

            'termination_reason' => fake()->randomElement([
                'Resignation',
                'End of contract',
                'Company restructuring',
                'Performance',
            ]),
        ]);
    }
}
