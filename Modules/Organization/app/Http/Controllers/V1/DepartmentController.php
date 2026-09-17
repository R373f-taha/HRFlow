<?php
namespace Modules\Organization\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Modules\Organization\Http\Requests\StoreDepartmentRequest;
//use Modules\Organization\Http\Requests\StoreDepartmentRequest;
// use Modules\Organization\Http\Requests\StoreDepartmentRequest as RequestsStoreDepartmentRequest;
use Modules\Organization\Http\Requests\UpdateDepartmentRequest;
use Modules\Organization\Models\Department;
use Modules\Organization\Services\V1\DepartmentService;
use Modules\Organization\Transformers\DepartmentResource;

class DepartmentController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        protected DepartmentService $departmentService
    ) {}

    /**
     * GET /api/v1/departments
     * List all departments (Hierarchical & Cached)
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Department::class);

        $departments = $this->departmentService->getAllDepartments();

        return response()->json([
            'success' => true,
            'message' => __('Departments list retrieved successfully.'),
            'data'    => DepartmentResource::collection($departments),
        ], 200);
    }

    /**
     * GET /api/v1/departments/{id}
     * Show single department details
     */
    public function show(int $id): JsonResponse
    {
        $departmentModel = $this->departmentService->findModel($id);

        if (!$departmentModel) {
            return response()->json([
                'success' => false,
                'message' => __('Department not found.'),
            ], 404);
        }

        $this->authorize('view', $departmentModel);

        $departmentData = $this->departmentService->getDepartmentById($id);

        return response()->json([
            'success' => true,
            'message' => __('Department details retrieved successfully.'),
            'data'    => new DepartmentResource($departmentData),
        ], 200);
    }
    /**
     * POST /api/v1/departments
     * Store new department
     */
    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $this->authorize('create', Department::class);

        $department = $this->departmentService->createDepartment($request->validated());

        return response()->json([
            'success' => true,
            'message' => __('Department created successfully.'),
            'data'    => new DepartmentResource($department),
        ], 201);
    }

    /**
     * PUT /api/v1/departments/{id}
     * Update department details
     */
    public function update(UpdateDepartmentRequest $request, int $id): JsonResponse
    {
        $departmentModel = $this->departmentService->findModel($id);

        if (!$departmentModel) {
            return response()->json([
                'success' => false,
                'message' => __('Department not found.'),
            ], 404);
        }

        $this->authorize('update', $departmentModel);

        $updatedDepartment = $this->departmentService->updateDepartment($departmentModel, $request->validated());

        return response()->json([
            'success' => true,
            'message' => __('Department updated successfully.'),
            'data'    => new DepartmentResource($updatedDepartment),
        ], 200);
    }

    /**
     * DELETE /api/v1/departments/{id}
     * Delete department
     */
    public function destroy(int $id): JsonResponse
    {
        $departmentModel = $this->departmentService->findModel($id);

        if (!$departmentModel) {
            return response()->json([
                'success' => false,
                'message' => __('Department not found.'),
            ], 404);
        }

        $this->authorize('delete', $departmentModel);

        $this->departmentService->deleteDepartment($departmentModel);

        return response()->json([
            'success' => true,
            'message' => __('Department deleted successfully.'),
        ], 204);
    }
}
