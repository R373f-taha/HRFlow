<?php

namespace Modules\Reports\Services\V1;

use App\Support\Cache\CacheSupport;
use Modules\Reports\Actions\GetAttendanceOverviewReportAction;
use Modules\Reports\Actions\GetHeadcountReportAction;
use Modules\Reports\Actions\GetLeaveUtilizationReportAction;
use Modules\Reports\Actions\GetPayrollSummaryReportAction;
use Modules\Reports\Actions\GetPerformanceSummaryReportAction;
use Modules\Reports\Actions\GetTurnoverReportAction;

class ReportService
{
    protected const CACHE_TTL = 3600; // 1 Hour

    public function getHeadcount(): array
    {
        return CacheSupport::remember('reports.headcount', self::CACHE_TTL, function () {
            return (new GetHeadcountReportAction())->execute();
        });
    }

    public function getPayrollSummary(?int $year, ?int $month): array
    {
        $cacheKey = "reports.payroll_summary.y{$year}_m{$month}";

        return CacheSupport::remember($cacheKey, self::CACHE_TTL, function () use ($year, $month) {
            return (new GetPayrollSummaryReportAction())->execute($year, $month);
        });
    }

    public function getLeaveUtilization(?int $year): array
    {
        $cacheKey = "reports.leave_utilization.y{$year}";

        return CacheSupport::remember($cacheKey, self::CACHE_TTL, function () use ($year) {
            return (new GetLeaveUtilizationReportAction())->execute($year);
        });
    }

    public function getAttendanceOverview(?string $startDate, ?string $endDate): array
    {
        $cacheKey = "reports.attendance_overview.s{$startDate}_e{$endDate}";

        return CacheSupport::remember($cacheKey, self::CACHE_TTL, function () use ($startDate, $endDate) {
            return (new GetAttendanceOverviewReportAction())->execute($startDate, $endDate);
        });
    }

    public function getTurnover(?int $year): array
    {
        $cacheKey = "reports.turnover.y{$year}";

        return CacheSupport::remember($cacheKey, self::CACHE_TTL, function () use ($year) {
            return (new GetTurnoverReportAction())->execute($year);
        });
    }

    public function getPerformanceSummary(?int $cycleId): array
    {
        $cacheKey = "reports.performance_summary.c{$cycleId}";

        return CacheSupport::remember($cacheKey, self::CACHE_TTL, function () use ($cycleId) {
            return (new GetPerformanceSummaryReportAction())->execute($cycleId);
        });
    }
}
