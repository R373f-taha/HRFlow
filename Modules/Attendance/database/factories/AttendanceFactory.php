<?php

namespace Modules\Attendance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Attendance\Models\Attendance;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $status = fake()->randomElement([
            'present',
            'present',
            'present',
            'absent',
            'late',
            'on_leave',
        ]);

        if ($status === 'absent') {
            return [
                'employee_id' => null,
                'date' => fake()->dateTimeBetween(
                    '-3 months',
                    'now'
                )->format('Y-m-d'),
                'check_in' => null,
                'check_out' => null,
                'status' => $status,
                'note' => 'Automatically recorded absence',
            ];
        }

        if ($status === 'on_leave') {
            return [
                'employee_id' => null,
                'date' => fake()->dateTimeBetween(
                    '-3 months',
                    'now'
                )->format('Y-m-d'),
                'check_in' => null,
                'check_out' => null,
                'status' => $status,
                'note' => 'Employee on leave',
            ];
        }

        return [
            'employee_id' => null,

            'date' => fake()->dateTimeBetween(
                '-3 months',
                'now'
            )->format('Y-m-d'),

            'check_in' => fake()->dateTimeBetween(
                '08:00',
                '09:30'
            ),

            'check_out' => fake()->dateTimeBetween(
                '16:00',
                '18:00'
            ),

            'status' => $status,

            'note' => fake()->optional()->sentence(),
        ];
    }
}
