<?php
namespace Modules\Reports\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Reports\Actions\GetAttendanceOverviewReportAction;

class GetAttendanceOverviewReportController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request, GetAttendanceOverviewReportAction $action): JsonResponse
    {
        $this->authorize('viewAny', 'Modules\Reports\Models\Report');

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        return response()->json([
            'data' => $action->execute($startDate, $endDate),
        ]);
    }
}
