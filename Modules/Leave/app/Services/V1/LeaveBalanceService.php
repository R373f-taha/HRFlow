<?php

namespace Modules\Leave\Services\V1;

use App\Support\Cache\CacheSupport;
use Modules\Leave\Models\LeaveBalance;
use Modules\Leave\Transformers\LeaveBalanceResource;

class LeaveBalanceService
{
    private const CACHE_TTL = 86400; // 24 Hours

    /**
     * Get employee balances for a specific year (cached with thundering herd protection).
     *
     * @return array<int, LeaveBalance>
     */
    public function getEmployeeBalances(int $employeeId, ?int $year = null): array
    {
        $year = $year ?? (int) date('Y');
        $cacheKey = $this->getCacheKey($employeeId, $year);

        return CacheSupport::remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($employeeId, $year) {
                $balances = LeaveBalance::with('leaveType')
                    ->where('employee_id', $employeeId)
                    ->where('year', $year)
                    ->get();

                return LeaveBalanceResource::collection($balances)->resolve();
            });
    }

    /**
     * Get specific balance for an employee and leave type.
     */
public function getBalance(int $employeeId, int $leaveTypeId, int $year): ?array
{
    $cacheKey = $this->getCacheKey($employeeId, $leaveTypeId);

    return CacheSupport::remember(
        $cacheKey,
        self::CACHE_TTL,
        function () use ($employeeId, $leaveTypeId, $year) {
            $balance = LeaveBalance::where('employee_id', $employeeId)
                ->where('leave_type_id', $leaveTypeId)
                ->where('year', $year)
                ->first();

            if (! $balance) {
                return null;
            }

            // ->resolve() transforms the Eloquent model into a clean PHP array!
            return (new LeaveBalanceResource($balance))->resolve();
        }
    );
}
    /**
     * Immediately invalidate cached balance for an employee.
     */
    public function invalidateCache(int $employeeId, ?int $year = null): void
    {
        $year = $year ?? (int) date('Y');
        CacheSupport::forget($this->getCacheKey($employeeId, $year));
    }

    private function getCacheKey(int $employeeId, int $year): string
    {
        return "leave_balance:employee_{$employeeId}:year_{$year}";
    }
}
