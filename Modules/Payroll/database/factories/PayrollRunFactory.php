<?php

namespace Modules\Payroll\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Payroll\Models\PayrollRun;

/**
 * @extends Factory<PayrollRun>
 */
class PayrollRunFactory extends Factory
{
    protected $model = PayrollRun::class;

    public function definition(): array
    {
        return [
            'year' => now()->year,

            'month' => fake()->numberBetween(1, 12),

            'status' => 'draft',

            'processed_at' => null,

            'finalized_at' => null,
        ];
    }
}
