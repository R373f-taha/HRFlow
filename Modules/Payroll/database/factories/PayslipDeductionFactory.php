<?php

namespace Modules\Payroll\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Payroll\Models\PayslipDeduction;

/**
 * @extends Factory<PayslipDeduction>
 */
class PayslipDeductionFactory extends Factory
{
    protected $model = PayslipDeduction::class;

    public function definition(): array
    {
        return [
            'payslip_id' => null,

            'name' => fake()->randomElement([
                'Tax',
                'Insurance',
                'Loan',
                'Late Penalty',
                'Other Deduction',
            ]),

            'reason' => fake()->sentence(),

            'amount' => fake()->randomFloat(
                2,
                25,
                500
            ),
        ];
    }
}
