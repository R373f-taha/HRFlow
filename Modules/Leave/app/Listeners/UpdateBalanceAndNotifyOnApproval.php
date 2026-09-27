<?php

namespace Modules\Leave\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Leave\Events\LeaveRequestApproved;
use Modules\Leave\Notifications\LeaveApprovedNotification;
use Modules\Leave\Services\V1\LeaveBalanceService;

class UpdateBalanceAndNotifyOnApproval implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(private LeaveBalanceService $balanceService) {}

    public function handle(LeaveRequestApproved $event): void
    {
        $leaveRequest = $event->leaveRequest->loadMissing(['employee.user', 'leaveType']);

        // 1. Invalidate employee balance cache immediately
        $this->balanceService->invalidateCache($leaveRequest->employee_id);

        // 2. Notify employee
        $user = $leaveRequest->employee?->user;
        if ($user) {
            $user->notify(new LeaveApprovedNotification($leaveRequest));
        }
    }
}
