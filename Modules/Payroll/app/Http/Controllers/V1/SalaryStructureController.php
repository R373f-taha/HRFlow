<?php

namespace Modules\Payroll\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Modules\Payroll\Http\Requests\StoreSalaryStructureRequest;
use Modules\Payroll\Http\Requests\UpdateSalaryStructureRequest;
use Modules\Payroll\Models\SalaryStructure;
use Modules\Payroll\Services\V1\SalaryStructureService;

class SalaryStructureController extends Controller
{
    use AuthorizesRequests;
    public function __construct(protected SalaryStructureService $SalaryStructureService) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', SalaryStructure::class);

        $structures = $this->SalaryStructureService->getPaginatedSalaryStructures(request()->all());

        return response()->json($structures);
    }

    public function store(StoreSalaryStructureRequest $request): JsonResponse
    {
        $this->authorize('create', SalaryStructure::class);

        $structure = $this->SalaryStructureService->createOrUpdateSalaryStructure($request->validated());

        return response()->json([
            'message' => 'Salary structure configured successfully.',
            'data' => $structure,
        ], 201);
    }
    public function update(int $id, UpdateSalaryStructureRequest $request): JsonResponse
    {
        $structure = SalaryStructure::findOrFail($id);
        $this->authorize('update', $structure);

        $updatedStructure = $this->SalaryStructureService->updateSalaryStructure($structure, $request->validated());

        return response()->json([
            'message' => 'Salary structure updated successfully.',
            'data' => $updatedStructure,
        ]);
    }
}
