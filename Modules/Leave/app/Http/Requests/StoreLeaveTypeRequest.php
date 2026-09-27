<?php

namespace Modules\Leave\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Leave\Models\LeaveType;

class StoreLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', LeaveType::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:leave_types,name'],
            'annual_days' => ['required', 'integer', 'min:1'],
            'is_paid' => ['boolean'],
            'requires_document' => ['boolean'],
        ];
    }
}
