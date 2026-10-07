<?php

namespace Modules\Payroll\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayslipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => data_get($this, 'id'),
            'payroll_run_id' => data_get($this, 'payroll_run_id'),
            'employee_id' => data_get($this, 'employee_id'),
            'basic_salary' => (float) data_get($this, 'basic_salary'),
            'total_allowances' => (float) data_get($this, 'total_allowances'),
            'total_deductions' => (float) data_get($this, 'total_deductions'),
            'unpaid_leave_deduction' => (float) data_get($this, 'unpaid_leave_deduction'),
            'net_salary' => (float) data_get($this, 'net_salary'),
            'is_read_only' => data_get($this, 'payrollRun.status') === 'finalized',
            'deductions' => PayslipDeductionResource::collection($this->whenLoaded('deductions')),
        ];
    }
}
