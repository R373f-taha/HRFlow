<?php

namespace Modules\Reports\Actions;

use App\Support\Cache\CacheSupport;
use Illuminate\Support\Facades\DB;

class GetAttendanceOverviewReportAction
{ protected const CACHE_TTL = 3600;
    public function execute(?string $startDate = null, ?string $endDate = null): array
    {

       $cacheKey = "reports.attendance_overview.s{$startDate}_e{$endDate}";

        return CacheSupport::remember($cacheKey, self::CACHE_TTL, function () use ($startDate, $endDate) {
        $query = DB::table('attendances');

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        $byStatus = $query->select('status', DB::raw('COUNT(id) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        $totalRecords = array_sum($byStatus);
        $presentCount = $byStatus['present'] ?? 0;
        $absentCount = $byStatus['absent'] ?? 0;
        $lateCount = $byStatus['late'] ?? 0;

        $attendanceRate = $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 2) : 0;

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_attendance_records' => $totalRecords,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'late_count' => $lateCount,
            'attendance_rate_percentage' => $attendanceRate,
            'breakdown' => $byStatus,
        ];
    });}
}
