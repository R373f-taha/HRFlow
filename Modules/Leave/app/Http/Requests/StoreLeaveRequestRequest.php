<?php

namespace Modules\Leave\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Leave\Models\LeaveRequest;
use Modules\Leave\Models\LeaveType;
use Modules\Leave\Rules\NoOverLappingLeave;

class StoreLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', LeaveRequest::class);
    }

    public function rules(): array
    {
        $employee = $this->user()->employee;
        $employeeId = $employee?->id;
        $startDate = $this->input('start_date');
        $endDate = $this->input('end_date');
        $leaveTypeId = $this->input('leave_type_id');

        // 1. Calculate duration and check leave type requirement
        $isDocumentRequired = false;

        if ($startDate && $endDate && $leaveTypeId) {
            $days = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;// +1 to include both start and end dates
            $leaveType = LeaveType::find($leaveTypeId);

            if ($leaveType && $leaveType->requires_document && $days > 3) {
                $isDocumentRequired = true;
            }
        }

        // 2. Dynamically assign 'required' or 'nullable'
        return [
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
                new NoOverLappingLeave($employeeId ?? 0, (string) $startDate, (string) $endDate),
            ],
            'document' => [
                $isDocumentRequired ? 'required' : 'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120', // Max file size: 5MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'document.required' => 'A supporting document is required for leave requests longer than 2 days for this leave type.',
        ];
    }
}
