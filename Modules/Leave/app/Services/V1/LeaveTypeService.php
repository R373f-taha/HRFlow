<?php

namespace Modules\Leave\Services\V1;

use App\Support\Cache\CacheSupport;
use Modules\Leave\Models\LeaveType;
use Modules\Leave\Transformers\LeaveTypeResource;

class LeaveTypeService
{
    private const CACHE_KEY = 'leave_types:all';

    private const CACHE_TTL = 86400; // 24 hours

    /**
     * Get all leave types formatted as an array for direct JSON output.
     * Caching the array structure avoids PHP serialization/unserialize class issues entirely.
     */
    public function getAll(): array
    {
        return CacheSupport::remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            function () {
                $types = LeaveType::all();

                // Convert resource collection to a raw array before writing to cache
                return LeaveTypeResource::collection($types)->resolve();
            }
        );
    }

    /**
     * Clear leave types cache key on create/update/delete.
     */
    public function invalidateCache(): void
    {
        CacheSupport::forget(self::CACHE_KEY);
    }
}
