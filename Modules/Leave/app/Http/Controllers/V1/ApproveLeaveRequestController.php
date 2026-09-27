<?php

namespace Modules\Leave\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Leave\Actions\ApproveLeaveRequestAction;
use Modules\Leave\Models\LeaveRequest;
use Modules\Leave\Transformers\LeaveRequestResource;
use RuntimeException;

class ApproveLeaveRequestController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(
        Request $request,
        LeaveRequest $leaveRequest,
        ApproveLeaveRequestAction $action
    ): JsonResponse {
        $this->authorize('approve', $leaveRequest);

        try {
            $approvedRequest = $action->execute($leaveRequest, $request->user());

            // Load the relationship explicitly on the model instance
            $approvedRequest->load('leaveType');

            return response()->json([
                'message' => 'Request approved successfully',
                'data' => new LeaveRequestResource($approvedRequest),
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
