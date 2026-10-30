<?php


namespace Modules\Reports\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Reports\Actions\GetPerformanceSummaryReportAction;
use Modules\Reports\Services\GetPerformanceSummaryReportService;

class GetPerformanceSummaryReportController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request, GetPerformanceSummaryReportAction $action): JsonResponse
    {
        $this->authorize('viewAny', 'Modules\Reports\Models\Report');

        $cycleId = $request->query('cycle_id') ? (int) $request->query('cycle_id') : null;

        return response()->json([
            'data' => $action->execute($cycleId),
        ]);
    }
}
