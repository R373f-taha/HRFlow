<?php
namespace Modules\Organization\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Organization\Models\JobTitle;

class StoreJobTitleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', JobTitle::class);
    }

    public function rules(): array
    {
        return [
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('job_titles')->where(function ($query) {
                    return $query->where('department_id', $this->department_id);
                }),
            ],
            'grade' => ['nullable', 'string', 'max:255'],
        ];
    }
}
