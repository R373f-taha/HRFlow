<?php

namespace Modules\Leave\Actions;

use Modules\Auth\Models\User;
use Modules\Leave\Models\LeaveRequest;

class RejectLeaveRequestAction
{
    public function execute(LeaveRequest $leaveRequest, User $rejecter, string $reason): LeaveRequest
    {
      if ($leaveRequest->status === 'approved') {
                throw new \RuntimeException('This request is already approved.');
            }
        if ($leaveRequest->status === 'rejected') {
                throw new \RuntimeException('This request is already rejected.');
            }

        $leaveRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        return $leaveRequest;
    }
}
