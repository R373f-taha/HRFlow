<?php

namespace Modules\Leave\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Employees\Resources\EmployeeResource;

class LeaveRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,

            // Resolve nested relationships into pure arrays!
            'leave_type' => $this->relationLoaded('leaveType') && $this->leaveType
                ? (new LeaveTypeResource($this->leaveType))->resolve()
                : null,

            'employee' => $this->relationLoaded('employee') && $this->employee
                ? (new EmployeeResource($this->employee))->resolve()
                : null,

            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'days_count' => $this->days_count,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
