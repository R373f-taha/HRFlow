<?php

namespace Modules\Attendance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Attendance\Enums\AttendanceStatus;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('hr-admin');
    }

    public function rules(): array
    {
        // Supports both single payload and array of payloads (Bulk)
        return [
            'records' => ['required', 'array', 'min:1'],
            'records.*.employee_id' => ['required', 'integer', 'exists:employees,id'],
            'records.*.date' => ['required', 'date_format:Y-m-d'],
            'records.*.check_in' => ['nullable', 'date_format:H:i:s'],
            'records.*.check_out' => ['nullable', 'date_format:H:i:s', 'after:records.*.check_in'],
            'records.*.status' => ['required', Rule::enum(AttendanceStatus::class)],
            'records.*.note' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Wrap single submission into "records" array transparently
        if ($this->has('employee_id') && ! $this->has('records')) {
            $this->merge([
                'records' => [$this->all()],
            ]);
        }
    }
}
