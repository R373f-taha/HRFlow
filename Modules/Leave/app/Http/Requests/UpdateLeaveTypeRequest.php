<?php

namespace Modules\Leave\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $leaveType = $this->route('leaveType');

        return $this->user()->can('update', $leaveType);
    }

    public function rules(): array
    {
        // // Extract ID safely whether route parameter is bound to a Model instance or raw ID string/int
        $leaveTypeId = $this->route('leaveType')?->id ?? $this->route('leaveType');

        return [
            'name' => ['sometimes', 'string', 'max:255', 'unique:leave_types,name,'.$leaveTypeId],
            'annual_days' => ['required', 'integer', 'min:1'],
            'is_paid' => ['boolean'],
            'requires_document' => ['boolean'],
        ];
    }
}
