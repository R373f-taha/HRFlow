<?php

namespace Modules\Attendance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Attendance\Enums\AttendanceStatus;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('hr-admin');
    }

    public function rules(): array
    {
        return [
            'check_in' => ['nullable', 'date_format:H:i:s'],
            'check_out' => ['nullable', 'date_format:H:i:s'],
            'status' => ['sometimes', Rule::enum(AttendanceStatus::class)],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
