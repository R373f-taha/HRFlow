<?php

namespace Modules\Organization\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth handled via DepartmentPolicy in controller
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['required', 'string', 'max:50', Rule::unique('departments', 'code')],
            'parent_id'   => ['nullable', 'integer', Rule::exists('departments', 'id')],
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
