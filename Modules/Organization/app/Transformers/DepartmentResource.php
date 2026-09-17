<?php

namespace Modules\Organization\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    /**
     * Transform the department model or cached array into API response structure.
     */
    public function toArray(Request $request): array
    {
        // Support both Array (Cache) and Eloquent Model Data
        $id = $this['id'] ?? $this->id ?? null;
        $name = $this['name'] ?? $this->name ?? null;
        $code = $this['code'] ?? $this->code ?? null;
        $createdAt = $this['created_at'] ?? $this->created_at ?? null;
        $updatedAt = $this['updated_at'] ?? $this->updated_at ?? null;

        // Manager relation parsing
        $managerData = $this['manager'] ?? $this->manager ?? null;
        $manager = null;
        if ($managerData) {
            $manager = [
                'id'              => $managerData['id'] ?? $managerData->id ?? null,
                'name'            => $managerData['user']['name'] ?? $managerData->user?->name ?? null,
                'employee_number' => $managerData['employee_number'] ?? $managerData->employee_number ?? null,
            ];
        }

        // Parent relation parsing
        $parentData = $this['parent'] ?? $this->parent ?? null;
        $parent = null;
        if ($parentData) {
            $parent = [
                'id'   => $parentData['id'] ?? $parentData->id ?? null,
                'name' => $parentData['name'] ?? $parentData->name ?? null,
                'code' => $parentData['code'] ?? $parentData->code ?? null,
            ];
        }

        // Employees relation parsing
        $employeesData = $this['employees'] ?? $this->employees ?? [];
        $employees = [];
        if (is_iterable($employeesData)) {
            foreach ($employeesData as $emp) {
                $employees[] = [
                    'id'              => $emp['id'] ?? $emp->id ?? null,
                    'name'            => $emp['user']['name'] ?? $emp->user?->name ?? null,
                    'employee_number' => $emp['employee_number'] ?? $emp->employee_number ?? null,
                    'status'          => $emp['status'] ?? $emp->status ?? null,
                ];
            }
        }

        return [
            'id'         => $id,
            'name'       => $name,
            'code'       => $code,
            'manager'    => $manager,
            'parent'     => $parent,
            'employees'  => $employees,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }
}
