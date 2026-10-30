<?php

namespace Modules\Reports\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Reports\Actions\GetLeaveUtilizationReportAction;

class GetLeaveUtilizationReportController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request, GetLeaveUtilizationReportAction $action): JsonResponse
    {
        $this->authorize('viewAny', 'Modules\Reports\Models\Report');

        $year = $request->query('year') ? (int) $request->query('year') : null;

        return response()->json([
            'data' => $action->execute($year),
        ]);
    }
}
