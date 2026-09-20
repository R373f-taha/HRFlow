<?php

namespace Modules\Employees\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \Modules\Employees\Models\Employee::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'job_title_id' => [
                'required',
                'integer',
                Rule::exists('job_titles', 'id')->where(function ($query) {
                    $query->where('department_id', $this->input('department_id'));
                }),
            ],
            'manager_id' => ['nullable', 'integer', 'exists:employees,id'],
            'employment_type' => ['required', 'string'],
            'hire_date' => ['required', 'date'],
            'national_id' => ['required', 'string', 'unique:employees,national_id'],
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
