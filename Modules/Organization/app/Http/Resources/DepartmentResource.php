<?php

namespace Modules\Organization\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Employees\Resources\EmployeeResource;

//use Modules\Employees\Http\Resources\EmployeeResource;

class DepartmentResource extends JsonResource
{
    /**
     * Transform the department into an API response.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,

            'manager' => $this->whenLoaded(
                'manager',
                fn () => $this->manager
                    ? [
                        'id' => $this->manager->id,
                        'name' => $this->manager->user?->name,
                        'employee_number' => $this->manager->employee_number,
                    ]
                    : null
            ),

            'parent' => $this->whenLoaded(
                'parent',
                fn () => $this->parent
                    ? [
                        'id' => $this->parent->id,
                        'name' => $this->parent->name,
                        'code' => $this->parent->code,
                    ]
                    : null
            ),

            'employees' => EmployeeResource::collection(
                $this->whenLoaded('employees')
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
