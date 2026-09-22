<?php
namespace Modules\Employees\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the employee into an API response.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_number' => $this->employee_number,

            'name' => $this->whenLoaded(
                'user',
                fn () => $this->user?->name
            ),

            'email' => $this->whenLoaded(
                'user',
                fn () => $this->user?->email
            ),

            'job_title' => $this->whenLoaded(
                'jobTitle',
                fn () => $this->jobTitle?->name
            ),

            'employment_type' => $this->employment_type,
           // 'hire_date' => $this->hire_date,
           'hire_date' => $this->hire_date ? $this->hire_date->format('Y-m-d') : null,
            'status' => $this->status,
        ];
    }
}
