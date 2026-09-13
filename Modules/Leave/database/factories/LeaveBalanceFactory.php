<?php

namespace Modules\Leave\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Leave\Models\LeaveBalance;

/**
 * @extends Factory<LeaveBalance>
 */
class LeaveBalanceFactory extends Factory
{
    protected $model = LeaveBalance::class;

    public function definition(): array
    {
        $allocated = fake()->randomFloat(
            2,
            15,
            30
        );

        $used = fake()->randomFloat(
            2,
            0,
            min($allocated, 12)
        );

        return [
            'employee_id' => null,
            'leave_type_id' => null,
            'year' => now()->year,
            'allocated_days' => $allocated,
            'used_days' => $used,
            'remaining_days' => round(
                $allocated - $used,
                2
            ),
        ];
    }
}
