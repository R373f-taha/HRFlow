<?php
namespace Modules\Reports\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Modules\Reports\Actions\GetHeadcountReportAction;
use Modules\Reports\Services\GetHeadcountReportService;

class GetHeadcountReportController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(GetHeadcountReportAction $action): JsonResponse
    {
        $this->authorize('viewAny', 'Modules\Reports\Models\Report');

        return response()->json([
            'data' => $action->execute(),
        ]);
    }
}
