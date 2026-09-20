<?php

namespace Modules\Payroll\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalaryHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalAllowances = (float) $this->housing_allowance
            + (float) $this->transport_allowance
            + (float) $this->other_allowances;

        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'basic_salary' => (float) $this->basic_salary,
            'allowances' => [
                'housing' => (float) $this->housing_allowance,
                'transport' => (float) $this->transport_allowance,
                'other' => (float) $this->other_allowances,
                'total' => $totalAllowances,
            ],
            'gross_salary' => (float) $this->basic_salary + $totalAllowances,
            'effective_from' => $this->effective_from,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
