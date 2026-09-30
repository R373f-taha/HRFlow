<?php

namespace Modules\Attendance\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'status' => $this['status'] ?? $this->status,
            'total_count' => (int) ($this['total_count'] ?? $this->total_count),
            'total_employees' => (int) ($this['total_employees'] ?? $this->total_employees),
        ];
    }
}
