<?php

namespace Modules\Payroll\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayslipDeductionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => data_get($this, 'id'),
            'name' => data_get($this, 'name'),
            'reason' => data_get($this, 'reason'),
            'amount' => (float) data_get($this, 'amount'),
        ];
    }
}
