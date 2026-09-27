<?php

namespace Modules\Leave\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Modules\Leave\Events\LeaveRequestSubmitted;
use Modules\Leave\Notifications\PendingLeaveApprovalNotification;

class SendLeaveRequestNotificationToManager implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(LeaveRequestSubmitted $event): void
    {
        $leaveRequest = $event->leaveRequest->loadMissing(['employee.manager.user', 'leaveType']);
        $managerUser = $leaveRequest->employee?->manager?->user;

        if (! $managerUser) {
            Log::info("Leave Request #{$leaveRequest->id} submitted without explicit manager user.");
            return;
        }

        // Send notification to manager's User model
        Notification::send($managerUser, new PendingLeaveApprovalNotification($leaveRequest));
    }
}
