<?php

namespace Modules\Performance\Services\V1;

use App\Support\Cache\CacheSupport;
use Modules\Auth\Models\User;
use Modules\Performance\Models\PerformanceReview;
use Modules\Performance\Transformers\PerformanceReviewResource;

class GetMyPerformanceReviewsService
{
    protected const CACHE_TTL = 3600;

    public function execute(User $user): ?array
    {
        $employee = $user->employee;

        if (! $employee) {
            return null;
        }

        $cacheKey = "employees.performance.user_{$employee->id}";

        return CacheSupport::remember($cacheKey, self::CACHE_TTL, function () use ($employee) {
            $reviews = PerformanceReview::with(['performanceCycle', 'manager'])
                ->where('employee_id', $employee->id)
                ->get();

            return PerformanceReviewResource::collection($reviews)->resolve();
        });
    }
}
