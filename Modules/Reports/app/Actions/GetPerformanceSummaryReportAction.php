<?php


namespace Modules\Reports\Actions;

use App\Support\Cache\CacheSupport;
use Illuminate\Support\Facades\DB;

class GetPerformanceSummaryReportAction
{
    protected const CACHE_TTL = 3600;
    public function execute(?int $cycleId = null): array
    {
        $cacheKey = "reports.performance_summary.c{$cycleId}";


        return CacheSupport::remember($cacheKey, self::CACHE_TTL, function () use ($cycleId) {

        $query = DB::table('performance_reviews');

        if ($cycleId) {
            $query->where('performance_cycle_id', $cycleId);
        }

        $ratingDistribution = $query->select('overall_rating', DB::raw('COUNT(id) as count'))
            ->groupBy('overall_rating')
            ->orderBy('overall_rating', 'asc')
            ->get()
            ->map(fn ($item) => (array) $item)
            ->toArray();

        $totalReviews = array_sum(array_column($ratingDistribution, 'count'));
        $avgRating = $totalReviews > 0 ? round($query->avg('overall_rating'), 2) : 0;

        return [
            'performance_cycle_id' => $cycleId,
            'total_reviews' => $totalReviews,
            'average_rating' => $avgRating,
            'rating_distribution' => $ratingDistribution,
        ];
    });}
}
