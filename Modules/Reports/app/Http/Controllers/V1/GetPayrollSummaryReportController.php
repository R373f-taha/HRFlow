<?php

namespace Modules\Reports\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Reports\Actions\GetPayrollSummaryReportAction;
use Modules\Reports\Services\GetPayrollSummaryReportService;

class GetPayrollSummaryReportController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request, GetPayrollSummaryReportAction $action): JsonResponse
    {
        $this->authorize('viewAny', 'Modules\Reports\Models\Report');

        $year = $request->query('year') ? (int) $request->query('year') : null;
        $month = $request->query('month') ? (int) $request->query('month') : null;

        return response()->json([
            'data' => $action->execute($year, $month),
        ]);
    }
}
