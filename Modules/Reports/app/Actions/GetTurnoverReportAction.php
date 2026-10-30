<?php


namespace Modules\Reports\Actions;

use App\Support\Cache\CacheSupport;
use Illuminate\Support\Facades\DB;

class GetTurnoverReportAction
{
     protected const CACHE_TTL = 3600;

    public function execute(?int $year = null): array
    {
        $year = $year ?? (int) date('Y');

      $cacheKey = "reports.turnover.y{$year}";

        return CacheSupport::remember($cacheKey, self::CACHE_TTL, function () use ($year) {
        $activeEmployees = DB::table('employees')->where('status', 'active')->count();

        $terminatedEmployees = DB::table('employees')
            ->whereNotNull('termination_date')
            ->whereYear('termination_date', $year)
            ->count();

        $avgHeadcount = ($activeEmployees + $terminatedEmployees) > 0
            ? ($activeEmployees + ($activeEmployees + $terminatedEmployees)) / 2
            : 1;

        $turnoverRate = round(($terminatedEmployees / $avgHeadcount) * 100, 2);

        $reasonsBreakdown = DB::table('employees')
            ->whereNotNull('termination_date')
            ->whereYear('termination_date', $year)
            ->select('termination_reason', DB::raw('COUNT(id) as count'))
            ->groupBy('termination_reason')
            ->get()
            ->toArray();

        return [
            'year' => $year,
            'active_employees' => $activeEmployees,
            'terminated_employees' => $terminatedEmployees,
            'turnover_rate_percentage' => $turnoverRate,
            'reasons_breakdown' => $reasonsBreakdown,
        ];
    });}
}
