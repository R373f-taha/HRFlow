<?php

namespace Modules\Employees\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TerminateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('terminate', $this->route('employee'));
    }

    public function rules(): array
    {
        return [
            'termination_date' => ['required', 'date', 'after_or_equal:today'],
            'termination_reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
