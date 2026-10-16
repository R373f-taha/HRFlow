<?php

namespace Modules\Performance\Services\V1;

use App\Support\Cache\CacheSupport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as ConcretePaginator;
use Modules\Performance\Models\PerformanceCycle;

class PerformanceCycleService
{
    /**
     * Cache TTL in seconds (1 hour).
     */
    protected int $cacheTtl = 3600;

    /**
     * Get paginated performance cycles safely via array serialization & hydration.
     */
    public function getPaginatedCycles(array $filters = []): LengthAwarePaginator
    {
        $page = (int) request('page', 1);
        $perPage = (int) ($filters['per_page'] ?? 15);
        $cacheKey = "performance_cycles:page_{$page}:per_{$perPage}";

        $raw = CacheSupport::remember($cacheKey, $this->cacheTtl, function () use ($perPage) {
            return PerformanceCycle::latest()
                ->paginate($perPage)
                ->toArray();
        });

        $items = PerformanceCycle::hydrate($raw['data']);

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

    /**
     * Flush performance cycles cache page key.
     */
    public function flushPageCache(int $page = 1, int $perPage = 15): void
    {
        $cacheKey = "performance_cycles:page_{$page}:per_{$perPage}";
        CacheSupport::forget($cacheKey);
    }
}
