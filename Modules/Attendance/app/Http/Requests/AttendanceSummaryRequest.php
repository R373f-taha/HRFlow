<?php

namespace Modules\Attendance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceSummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'department_id' => (int) $this->query('department_id'),
            'year' => (int) $this->query('year'),
            'month' => (int) $this->query('month'),
        ]);
    }

    public function rules(): array
    {
        return [
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'year' => ['required', 'integer', 'digits:4'],
            'month' => ['required', 'integer', 'between:1,12'],
        ];
    }
}
