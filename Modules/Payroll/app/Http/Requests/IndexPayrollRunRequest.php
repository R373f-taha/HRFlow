<?php

namespace Modules\Payroll\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexPayrollRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'year' => ['sometimes', 'integer', 'digits:4'],
            'month' => ['sometimes', 'integer', 'between:1,12'],
            'status' => ['sometimes', 'string', 'in:draft,processing,finalized'],
        ];
    }
}
