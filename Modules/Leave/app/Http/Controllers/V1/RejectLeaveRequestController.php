<?php

namespace Modules\Leave\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Leave\Actions\RejectLeaveRequestAction;
use Modules\Leave\Http\Requests\RejectLeaveRequestRequest;
use Modules\Leave\Models\LeaveRequest;
use Modules\Leave\Transformers\LeaveRequestResource;
use RuntimeException;

class RejectLeaveRequestController extends Controller
{
    use AuthorizesRequests;
    public function __invoke(
        RejectLeaveRequestRequest $request,
        LeaveRequest $leaveRequest,
        RejectLeaveRequestAction $action
    ): JsonResponse {
        $this->authorize('reject', $leaveRequest);

        try {
            $rejectedRequest = $action->execute(
                $leaveRequest,
                $request->user(),
                $request->validated('rejection_reason')
            );

            return response()->json([
                'message' => 'Leave request rejected successfully.',
                'data' => new LeaveRequestResource($rejectedRequest),
            ]);
        } catch (RuntimeException $e) {
            // Converts domain business logic failures to a 422 client error
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
