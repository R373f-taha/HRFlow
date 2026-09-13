<?php

namespace Modules\Performance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Performance\Models\PerformanceReview;

/**
 * @extends Factory<PerformanceReview>
 */
class PerformanceReviewFactory extends Factory
{
    protected $model = PerformanceReview::class;

    public function definition(): array
    {
        return [
            'performance_cycle_id' => null,

            'employee_id' => null,

            'manager_id' => null,

            'overall_rating' => fake()->numberBetween(
                1,
                5
            ),

            'strengths' => fake()->paragraph(),

            'areas_for_improvement' => fake()->paragraph(),

            'goals' => fake()->paragraph(),
        ];
    }
}
