<?php

namespace Modules\Payroll\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Modules\Payroll\Actions\FinalizePayrollRunAction;
use Modules\Payroll\Actions\ProcessPayrollRunAction;
use Modules\Payroll\Http\Requests\IndexPayrollRunRequest;
use Modules\Payroll\Http\Requests\StorePayrollRunRequest;
use Modules\Payroll\Models\PayrollRun;
use Modules\Payroll\Services\V1\PayrollService;
use Modules\Payroll\Transformers\PayslipResource;

class PayrollRunController extends Controller
{use AuthorizesRequests;
    public function __construct(protected PayrollService $payrollService) {}
public function index(IndexPayrollRunRequest $request): JsonResponse
{
    $this->authorize('viewAny', PayrollRun::class);

    $runs = $this->payrollService->getPaginatedRuns($request->validated());

    return response()->json($runs);
}

    public function store(StorePayrollRunRequest $request): JsonResponse
    {
        $this->authorize('create', PayrollRun::class);

        $run = PayrollRun::create($request->validated());
        $this->payrollService->flushCache();

        return response()->json(['message' => 'Payroll run created', 'data' => $run], 201);
    }

   
}
