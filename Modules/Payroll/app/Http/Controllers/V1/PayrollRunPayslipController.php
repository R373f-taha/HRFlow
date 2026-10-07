<?php

namespace Modules\Payroll\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Modules\Payroll\Models\PayrollRun;
use Modules\Payroll\Services\V1\PayrollService;
use Modules\Payroll\Transformers\PayslipResource;

class PayrollRunPayslipController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected PayrollService $payrollService
    ) {}

    public function index(int $id): JsonResponse
    {
        $run = PayrollRun::findOrFail($id);
        $this->authorize('viewPayslips', $run);

        $payslips = $this->payrollService->getPaginatedPayslipsForRun($run);

        return response()->json(PayslipResource::collection($payslips));
    }
}
