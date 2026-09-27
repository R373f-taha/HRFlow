<?php

namespace Modules\Leave\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Employees\Models\Employee;
use Modules\Leave\Models\LeaveRequest;
use Modules\Leave\Services\V1\LeaveBalanceService;

class LeaveBalanceController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private LeaveBalanceService $balanceService) {}

    public function myBalance(): JsonResponse
    {
        $this->authorize('viewBalance', LeaveRequest::class);

        $employee = Auth::user()->employee;

        $balances = $this->balanceService->getEmployeeBalances($employee->id); // id from the authenticated user

        return response()->json(['data' => $balances]);
    }

    public function employeeBalance(Employee $employee): JsonResponse
    {
        $this->authorize('viewEmployeeBalance', LeaveRequest::class);

        $balances = $this->balanceService->getEmployeeBalances($employee->id);

        return response()->json(['data' => $balances]);
    }
}
