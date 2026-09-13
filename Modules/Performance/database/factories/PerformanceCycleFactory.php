<?php

namespace Modules\Performance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Performance\Models\PerformanceCycle;

/**
 * @extends Factory<PerformanceCycle>
 */
class PerformanceCycleFactory extends Factory
{
    protected $model = PerformanceCycle::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Annual Performance Review',
                'Mid-Year Performance Review',
                'Quarterly Performance Review',
            ]) . ' ' . now()->year,

            'starts_at' => now()
                ->subMonths(6)
                ->startOfMonth()
                ->format('Y-m-d'),

            'ends_at' => now()
                ->subMonths(3)
                ->endOfMonth()
                ->format('Y-m-d'),

            'status' => 'closed',
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => 'active',
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => 'draft',
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'status' => 'closed',
        ]);
    }
}
