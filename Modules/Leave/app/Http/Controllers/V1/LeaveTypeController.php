<?php

namespace Modules\Leave\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Leave\Http\Requests\StoreLeaveTypeRequest;
use Modules\Leave\Http\Requests\UpdateLeaveTypeRequest;
use Modules\Leave\Models\LeaveType;
use Modules\Leave\Services\V1\LeaveTypeService;
use Modules\Leave\Transformers\LeaveTypeResource;

class LeaveTypeController extends Controller
{
    public function __construct(private LeaveTypeService $leaveTypeService) {}

    public function index(): JsonResponse
    {
        $types = $this->leaveTypeService->getAll();

        return response()->json(['data' => $types]);
    }

    public function store(StoreLeaveTypeRequest $request): JsonResponse
    {
        $leaveType = LeaveType::create($request->validated());
        $this->leaveTypeService->invalidateCache();

        return response()->json([
            'message' => 'Leave type created successfully.',
            'data' => new LeaveTypeResource($leaveType),
        ], 201);
    }

    public function update(UpdateLeaveTypeRequest $request, LeaveType $leaveType): JsonResponse
    {
        $leaveType->update($request->validated());
        $this->leaveTypeService->invalidateCache();

        return response()->json([
            'message' => 'Leave type updated successfully.',
            'data' => new LeaveTypeResource($leaveType),
        ]);
    }
}
