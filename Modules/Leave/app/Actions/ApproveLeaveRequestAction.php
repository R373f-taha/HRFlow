<?php

namespace Modules\Leave\Actions;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Leave\Events\LeaveRequestApproved;
use Modules\Leave\Models\LeaveBalance;
use Modules\Leave\Models\LeaveRequest;
use Modules\Leave\Services\V1\LeaveBalanceService;
use RuntimeException;

class ApproveLeaveRequestAction
{
    public function __construct(private LeaveBalanceService $balanceService) {}

    public function execute(LeaveRequest $leaveRequest, User $approver): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest, $approver) {
            if ($leaveRequest->status === 'approved') {
                throw new RuntimeException('This request is already approved.');
            }

            $year = (int) Carbon::parse($leaveRequest->start_date)->format('Y');
            $balance = $this->balanceService->getBalance(
                $leaveRequest->employee_id,
                $leaveRequest->leave_type_id,
                $year
            );

            if (! $balance || $balance['remaining_days'] < $leaveRequest->days_count) {
                throw new RuntimeException('Remaining leave balance is insufficient for this request.');
            }

            if ($approver->hasRole('hr-admin')) {
                $leaveRequest->admin_approved_by = $approver->id;
                $leaveRequest->admin_approved_at = now();
            } else {
                $leaveRequest->manager_approved_by = $approver->id;
                $leaveRequest->manager_approved_at = now();
            }

            $leaveRequest->status = 'approved';
            $leaveRequest->save();

            LeaveBalance::where('employee_id', $leaveRequest->employee_id)
                ->where('leave_type_id', $leaveRequest->leave_type_id)
                ->where('year', $year)
                ->update([
                    'used_days' => DB::raw("used_days + {$leaveRequest->days_count}"),
                    'remaining_days' => DB::raw("remaining_days - {$leaveRequest->days_count}"),
                ]);

            $this->balanceService->invalidateCache($leaveRequest->employee_id, $year);

            event(new LeaveRequestApproved($leaveRequest));

            return $leaveRequest;
        });
    }
}
