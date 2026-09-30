<?php
namespace Modules\Attendance\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ?? data_get($this, 'id'),
            'employee_id' => $this->employee_id ?? data_get($this, 'employee_id'),
            'employee' => $this->whenLoaded('employee', function () {
                return [
                    'id' => data_get($this->employee, 'id'),
                    'first_name' => data_get($this->employee, 'first_name'),
                    'last_name' => data_get($this->employee, 'last_name'),
                ];
            }),
            'date' => is_string($this->date) ? $this->date : $this->date?->format('Y-m-d'),
            'check_in' => is_string($this->check_in) ? $this->check_in : $this->check_in?->format('H:i:s'),
            'check_out' => is_string($this->check_out) ? $this->check_out : $this->check_out?->format('H:i:s'),
            'status' => $this->status ?? data_get($this, 'status'),
            'note' => $this->note ?? data_get($this, 'note'),
            'created_at' => is_string($this->created_at) ? $this->created_at : $this->created_at?->toIso8601String(),
        ];
    }

}
