<?php

namespace Modules\Performance\Actions;

use App\Support\Cache\CacheSupport;
use Illuminate\Validation\ValidationException;
use Modules\Employees\Models\Employee;
use Modules\Performance\Models\PerformanceCycle;
use Modules\Performance\Models\PerformanceReview;

class CreatePerformanceReviewAction
{
    public function execute(Employee $manager, array $data): PerformanceReview

   {
      $alreadyReviewed = PerformanceReview::where('performance_cycle_id', $data['performance_cycle_id'])
            ->where('employee_id', $data['employee_id'])
            ->where('manager_id', $manager->id)
            ->exists();

        if ($alreadyReviewed) {
            throw ValidationException::withMessages([
                'employee_id' => ['A performance review for this employee in the selected cycle already exists.'],
            ]);
        }
        $review = PerformanceReview::create([
            'performance_cycle_id' => $data['performance_cycle_id'],
            'employee_id' => $data['employee_id'],
            'manager_id' => $manager->id,
            'overall_rating' => $data['overall_rating'],
            'strengths' => $data['strengths'] ?? null,
            'areas_for_improvement' => $data['areas_for_improvement'] ?? null,
            'goals' => $data['goals'] ?? null,
        ]);

        CacheSupport::forget("employees.performance.user_{$data['employee_id']}");

        if ($review->employee?->user_id) {
            CacheSupport::forget("performance_reviews:user_{$review->employee->user_id}:page_1:per_15");
        }

        return $review;
    }
}
