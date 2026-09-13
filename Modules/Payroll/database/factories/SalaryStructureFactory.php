<?php

namespace Modules\Payroll\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Payroll\Models\SalaryStructure;

/**
 * @extends Factory<SalaryStructure>
 */
class SalaryStructureFactory extends Factory
{
    protected $model = SalaryStructure::class;

    public function definition(): array
    {
        return [
            'employee_id' => null,

            'basic_salary' => fake()->randomFloat(
                2,
                800,
                5000
            ),

            'housing_allowance' => fake()->randomFloat(
                2,
                100,
                1000
            ),

            'transport_allowance' => fake()->randomFloat(
                2,
                50,
                500
            ),

            'other_allowances' => fake()->randomFloat(
                2,
                0,
                500
            ),

            'effective_from' => fake()->dateTimeBetween(
                '-2 years',
                '-1 month'
            )->format('Y-m-d'),
        ];
    }
}
