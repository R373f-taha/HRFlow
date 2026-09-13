<?php

namespace Modules\Leave\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Leave\Models\LeaveRequest;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween(
            '-6 months',
            '+3 months'
        );

        $days = fake()->numberBetween(1, 5);

        $end = (clone $start)->modify(
            '+' . ($days - 1) . ' days'
        );

        return [
            'employee_id' => null,

            'leave_type_id' => null,

            'start_date' => $start->format('Y-m-d'),

            'end_date' => $end->format('Y-m-d'),

            'days_count' => $days,

            'status' => 'pending',

            'manager_approved_by' => null,

            'manager_approved_at' => null,

            'admin_approved_by' => null,

            'admin_approved_at' => null,

            'rejection_reason' => null,

            'document_path' => null,
        ];
    }
}
