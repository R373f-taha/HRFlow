<?php

namespace Modules\Leave\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Leave\Models\LeaveType;

/**
 * @extends Factory<LeaveType>
 */
class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Annual Leave',
                'Sick Leave',
                'Emergency Leave',
                'Unpaid Leave',
                'Maternity Leave',
            ]),

            'annual_days' => 20,

            'is_paid' => true,

            'requires_document' => false,
        ];
    }
}
