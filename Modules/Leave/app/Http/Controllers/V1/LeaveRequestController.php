<?php

namespace Modules\Leave\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Leave\Actions\SubmitLeaveRequestAction;
use Modules\Leave\Http\Requests\StoreLeaveRequestRequest;
use Modules\Leave\Models\LeaveRequest;
use Modules\Leave\Services\V1\LeaveRequestService;
use Modules\Leave\Transformers\LeaveRequestResource;

class LeaveRequestController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private LeaveRequestService $leaveRequestService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $page = (int) $request->query('page', 1);
        $requests = $this->leaveRequestService->getPaginatedForUser($request->user(), $page);

        return response()->json($requests);
    }

    public function store(StoreLeaveRequestRequest $request, SubmitLeaveRequestAction $action): JsonResponse
    {
        $employee = $request->user()->employee;
        $leaveRequest = $action->execute(
            $employee->id,
            $request->validated(),
            $request->file('document')
        );

        return response()->json([
            'message' => 'Leave request submitted successfully.',
            'data' => new LeaveRequestResource($leaveRequest->load('leaveType')),
        ], 201);
    }

    public function show(LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('view', $leaveRequest);

        return response()->json([
            'data' => new LeaveRequestResource($leaveRequest->load(['leaveType', 'employee'])),
        ]);
    }

    public function destroy(LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('delete', $leaveRequest);

        $employeeId = $leaveRequest->employee_id;
        $leaveRequest->delete();

        $this->leaveRequestService->invalidateCache($employeeId);

        return response()->json(['message' => 'You Cancelled Your Leave request successfully '],204);
    }
}
