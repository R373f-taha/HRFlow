<?php

namespace Modules\Organization\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $department = $this->route('department');
        $departmentId = is_object($department) ? $department->id : $department;

        return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'code'        => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('departments', 'code')->ignore($departmentId)
            ],
            'parent_id'   => [
                'nullable',
                'integer',
                Rule::exists('departments', 'id'),
                Rule::notIn([$departmentId])
            ],
            'manager_id'  => ['nullable', 'integer', Rule::exists('employees', 'id')],
            'employees'   => ['nullable', 'array'],
            'employees.*' => ['required', 'integer', Rule::exists('employees', 'id'), 'distinct'],
        ];
    }

    public function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim($this->code)),
            ]);
        }
    }
}
