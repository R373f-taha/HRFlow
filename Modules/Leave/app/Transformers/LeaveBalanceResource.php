<?php

namespace Modules\Leave\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveBalanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'leave_type' => new LeaveTypeResource($this->whenLoaded('leaveType')),
            'year' => $this->year,
            'allocated_days' => (float) $this->allocated_days,
            'used_days' => (float) $this->used_days,
            'remaining_days' => (float) $this->remaining_days,
        ];
    }
}
