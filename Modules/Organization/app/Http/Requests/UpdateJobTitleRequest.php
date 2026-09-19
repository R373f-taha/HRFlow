<?php

namespace Modules\Organization\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobTitleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('job_title'));
    }

    public function rules(): array
    {
        $jobTitleId = $this->route('job_title')?->id ?? $this->route('job_title');
        $departmentId = $this->input('department_id', $this->route('job_title')?->department_id);

        return [
            'department_id' => ['sometimes', 'integer', 'exists:departments,id'],
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('job_titles')
                    ->where(fn ($query) => $query->where('department_id', $departmentId))
                    ->ignore($jobTitleId),
            ],
            'grade' => ['nullable', 'string', 'max:255'],
        ];
    }
}
