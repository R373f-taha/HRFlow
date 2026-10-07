<?php

namespace Modules\Payroll\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use DomainException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Payroll\Actions\AddPayslipDeductionAction;
use Modules\Payroll\Http\Requests\StoreDeductionRequest;
use Modules\Payroll\Models\Payslip;
use Modules\Payroll\Services\V1\PayrollService;
use Modules\Payroll\Transformers\PayslipDeductionResource;
use Modules\Payroll\Transformers\PayslipResource;

class PayslipController extends Controller
{
    use AuthorizesRequests;
    public function __construct(protected PayrollService $payrollService) {}

    public function myPayslips(): JsonResponse
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            return response()->json(['message' => 'Employee profile not found.'], 404);
        }

        $payslips = $this->payrollService->getPaginatedEmployeePayslips($employee->id);
        return response()->json($payslips);
    }

    public function show(int $id): JsonResponse
    {
        $payslip = Payslip::with(['payrollRun', 'deductions'])->findOrFail($id);
        $this->authorize('payslips.view', $payslip);

        return response()->json(new PayslipResource($payslip));
    }

    public function addDeduction(int $id, StoreDeductionRequest $request, AddPayslipDeductionAction $action): JsonResponse
    {

        $payslip = Payslip::with('payrollRun')->findOrFail($id);
        $this->authorize('applyDeduction', $payslip);

        try{
        $deduction = $action($payslip, $request->validated());

        return response()->json([
            'message' => 'Deduction added successfully',
            'data' => new PayslipDeductionResource($deduction),
        ], 201);
        }

        catch (DomainException  $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
