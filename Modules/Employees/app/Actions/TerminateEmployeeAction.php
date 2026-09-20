<?php

namespace Modules\Employees\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Employees\Enums\EmployeeStatus;
use Modules\Employees\Models\Employee;

class TerminateEmployeeAction
{
    public function execute(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data) {
            $employee->update([
                'status' => EmployeeStatus::TERMINATED->value,
                'termination_date' => $data['termination_date'],
                'termination_reason' => $data['termination_reason'],
            ]);

            // Deactivate user access immediately so terminated employee cannot log in
            $employee->user()->update([
                'is_active' => false,
            ]);

            return $employee;
        });
    }
}
