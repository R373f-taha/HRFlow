<?php

namespace Modules\Employees\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Employees\Actions\CreateEmployeeAction;
use Modules\Employees\Actions\TerminateEmployeeAction;
use Modules\Employees\Actions\UpdateEmployeeAction;
use Modules\Employees\Http\Requests\StoreEmployeeRequest;
use Modules\Employees\Http\Requests\TerminateEmployeeRequest;
use Modules\Employees\Http\Requests\UpdateEmployeeRequest;
use Modules\Employees\Models\Employee;
use Modules\Employees\Resources\EmployeeResource;
use Modules\Employees\Services\V1\GetEmployeesService;

class EmployeesController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, GetEmployeesService $service)
    {
        $this->authorize('viewAny', Employee::class);

        $filters = $request->only(['department_id', 'status', 'search', 'page', 'per_page']);

        return response()->json($service->execute($filters));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeRequest $request, CreateEmployeeAction $action): JsonResponse
    {
        $this->authorize('create', Employee::class);

        $employee = $action->execute($request->validated());

        return (new EmployeeResource($employee))
            ->response()
            ->setStatusCode(201);
    }



    public function show(Employee $employee, GetEmployeesService $service): JsonResponse
{
    $this->authorize('view', $employee);

    $cachedEmployee = $service->getById($employee->id);

    return response()->json([
        'data' => $cachedEmployee
    ]);
}
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee, UpdateEmployeeAction $action): EmployeeResource
    {
        $updated = $action->execute($employee, $request->validated());

        return new EmployeeResource($updated);
    }

    /**
     * Terminate the specified employee (Custom CRUD action).
     */
    public function terminate(TerminateEmployeeRequest $request, Employee $employee, TerminateEmployeeAction $action): JsonResponse
    {
        $this->authorize('terminate', $employee);

        $terminated = $action->execute($employee, $request->validated());

        return response()->json([
            'message' => 'Employee service terminated successfully.',
            'data' => new EmployeeResource($terminated),
        ]);
    }
}
