<?php

namespace Modules\Leave\Services\V1;
use Illuminate\Pagination\LengthAwarePaginator as ConcretePaginator;
use App\Support\Cache\CacheSupport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Auth\Models\User;
use Modules\Leave\Models\LeaveRequest;
use Modules\Leave\Transformers\LeaveRequestResource;

class LeaveRequestService
{
    private const CACHE_TTL = 3600; // 1 Hour

   /**
     * Get paginated leave requests based on user role and apply caching.
     */
    public function getPaginatedForUser(User $user, int $page = 1, int $perPage = 15): array
    {
        $employeeId = $user->employee?->id;
        $isEmployeeOnly = $user->hasRole('employee') && ! $user->hasAnyRole(['hr-admin', 'department-manager']);

        $cacheKey = $isEmployeeOnly
            ? "leave_requests:employee_{$employeeId}:page_{$page}:per_page_{$perPage}"
            : "leave_requests:all:page_{$page}:per_page_{$perPage}";

      return CacheSupport::remember($cacheKey, self::CACHE_TTL, function () use ($isEmployeeOnly, $employeeId, $perPage) {
        return LeaveRequest::with(['leaveType', 'employee'])
        ->when($isEmployeeOnly, fn ($query) => $query->where('employee_id', $employeeId))
        ->latest()
        ->paginate($perPage)
        ->toArray(); // Pure array - 100% safe from __PHP_Incomplete_Class
       });
    }
    /**
     * Invalidate leave request lists cache.
     */
    public function invalidateCache(?int $employeeId = null): void
    {
        // Flush specific employee cache keys or use tag/pattern invalidation if available
        if ($employeeId) {
            CacheSupport::forget("leave_requests:employee_{$employeeId}:page_1");
        }

        CacheSupport::forget('leave_requests:all:page_1');
    }
}
