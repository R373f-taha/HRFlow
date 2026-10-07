<?php

namespace Modules\Payroll\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use DomainException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Modules\Payroll\Actions\ProcessPayrollRunAction;
use Modules\Payroll\Models\PayrollRun;

class ProcessPayrollRunController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(int $id, ProcessPayrollRunAction $action): JsonResponse
    {
        $run = PayrollRun::findOrFail($id);
        $this->authorize('process', $run);

        try {
            $processedRun = $action($run);

            return response()->json([
                'message' => 'Payroll processed successfully.',
                'data' => $processedRun,
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
