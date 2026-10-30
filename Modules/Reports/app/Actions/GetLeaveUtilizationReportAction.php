<?php

namespace Modules\Reports\Actions;

use App\Support\Cache\CacheSupport;
use Illuminate\Support\Facades\DB;

class GetLeaveUtilizationReportAction
{protected const CACHE_TTL = 3600;
    public function execute(?int $year = null): array
    {
         $cacheKey = "reports.leave_utilization.y{$year}";


        $year = $year ?? (int) date('Y');

     return CacheSupport::remember($cacheKey, self::CACHE_TTL, function () use ($year) {
        $byLeaveType = DB::table('leave_requests')
            ->join('leave_types', 'leave_requests.leave_type_id', '=', 'leave_types.id')
            ->where('leave_requests.status', 'approved')
            ->whereYear('leave_requests.start_date', $year)
            ->select(
                'leave_types.name as leave_type',
                DB::raw('SUM(leave_requests.days_count) as total_days_taken'),
                DB::raw('COUNT(leave_requests.id) as total_requests')
            )
            ->groupBy('leave_types.id', 'leave_types.name')
            ->get()
            ->map(fn ($item) => (array) $item)
            ->toArray();

        $overallTaken = DB::table('leave_requests')
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->sum('days_count');

        return [
            'year' => $year,
            'total_approved_leave_days' => (float) $overallTaken,
            'by_leave_type' => $byLeaveType,
        ];
    });}
}
