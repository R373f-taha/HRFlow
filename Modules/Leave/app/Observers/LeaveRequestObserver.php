<?php

namespace Modules\Leave\Observers;

use Carbon\Carbon;
use Modules\Leave\Models\LeaveRequest;

class LeaveRequestObserver
{
    /**
     * Handle the LeaveRequest "creating" event.
     * Automatically calculates the inclusive days count before saving.
     */
    public function creating(LeaveRequest $leaveRequest): void
    {
        $this->calculateDaysCount($leaveRequest);
    }

    /**
     * Handle the LeaveRequest "updating" event.
     * Recalculates days count if start_date or end_date are modified.
     */
    public function updating(LeaveRequest $leaveRequest): void
    {
        if ($leaveRequest->isDirty(['start_date', 'end_date'])) {
            $this->calculateDaysCount($leaveRequest);
        }
    }

    /**
     * Calculate inclusive days between start_date and end_date.
     *
     * Example:
     * 2026-10-01 to 2026-10-01 = 1 day
     * 2026-10-01 to 2026-10-03 = 3 days
     */
    private function calculateDaysCount(LeaveRequest $leaveRequest): void
    {
        if ($leaveRequest->start_date && $leaveRequest->end_date) {
            $start = Carbon::parse($leaveRequest->start_date);
            $end = Carbon::parse($leaveRequest->end_date);

            $leaveRequest->days_count = $start->diffInDays($end) + 1;
        }
    }
}
