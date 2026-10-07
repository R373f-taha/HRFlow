<?php

namespace Modules\Payroll\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use DomainException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Modules\Payroll\Actions\FinalizePayrollRunAction;
use Modules\Payroll\Models\PayrollRun;

class FinalizePayrollRunController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(int $id, FinalizePayrollRunAction $action): JsonResponse
    {
        $run = PayrollRun::findOrFail($id);
        $this->authorize('finalize', $run);

        try {
            $finalizedRun = $action($run);

            return response()->json([
                'message' => 'Payroll finalized successfully.',
                'data' => $finalizedRun,
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
