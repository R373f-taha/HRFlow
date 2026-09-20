<?php

namespace Modules\Employees\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('employee'));
    }

    public function rules(): array
    {
        $employee = $this->route('employee');

        // Determine department_id to validate job_title_id against (either newly passed or existing)
        $departmentId = $this->input('department_id', $employee?->department_id);

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($employee?->user_id),
            ],
            'employee_number' => [
            'sometimes',
            'string',
            'max:50',
            Rule::unique('employees', 'employee_number')->ignore($employee?->id),
        ],
            'department_id' => ['sometimes', 'integer', 'exists:departments,id'],
            'job_title_id' => [
                'sometimes',
                'integer',
                Rule::exists('job_titles', 'id')->where(function ($query) use ($departmentId) {
                    $query->where('department_id', $departmentId);
                }),
            ],
            'manager_id' => ['nullable', 'integer', 'exists:employees,id'],
            'employment_type' => ['sometimes', 'string'],
            'hire_date' => ['sometimes', 'date'],
            'national_id' => [
                'sometimes',
                'string',
                Rule::unique('employees', 'national_id')->ignore($employee?->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'job_title_id.exists' => 'The selected job title does not belong to the specified department.',
        ];
    }
}
