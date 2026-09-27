<?php

namespace Modules\Leave\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Leave\Models\LeaveRequest;

class LeaveRequestApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(public LeaveRequest $leaveRequest) {}
}
