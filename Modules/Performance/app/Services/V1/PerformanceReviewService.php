<?php
namespace Modules\Performance\Services\V1;

use App\Support\Cache\CacheSupport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as ConcretePaginator;
use Modules\Auth\Models\User;
use Modules\Performance\Models\PerformanceReview;

class PerformanceReviewService
{
    protected int $cacheTtl = 3600;

    public function getPaginatedReviews(User $user, array $filters = []): LengthAwarePaginator
    {
        $page = (int) request('page', 1);
        $perPage = (int) ($filters['per_page'] ?? 15);
        $cacheKey = "performance_reviews:user_{$user->id}:page_{$page}:per_{$perPage}";

        $raw = CacheSupport::remember($cacheKey, $this->cacheTtl, function () use ($user, $perPage) {
            return PerformanceReview::query()
                ->forUser($user)
                ->with(['employee', 'manager', 'performanceCycle'])
                ->latest()
                ->paginate($perPage)
                ->toArray();
        });

        $items = PerformanceReview::hydrate($raw['data']);
        $items->loadMissing(['employee', 'manager', 'performanceCycle']);

        return resolve(ConcretePaginator::class, [
            'items' => $items,
            'total' => $raw['total'],
            'perPage' => $raw['per_page'],
            'currentPage' => $raw['current_page'],
            'options' => [
                'path' => ConcretePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ],
        ]);
    }
}
