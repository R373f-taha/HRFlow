<?php

namespace Modules\Employees\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Auth\Models\User;
use Modules\Employees\Enums\EmployeeStatus;

use Modules\Employees\Events\EmployeeCreated;
use Modules\Employees\Models\Employee;

class CreateEmployeeAction
{
    public function execute(array $data): Employee
    {
        return DB::transaction(function () use ($data) {
            $temporaryPassword = Str::random(10);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $temporaryPassword,
                'is_active' => true,
            ]);

            $user->assignRole('employee');

            $employeeNumber = 'EMP-' . strtoupper(Str::random(6));

            $employee = Employee::create([
                'user_id' => $user->id,
                'employee_number' => $employeeNumber,
                'department_id' => $data['department_id'],
                'job_title_id' => $data['job_title_id'],
                'manager_id' => $data['manager_id'] ?? null,
                'employment_type' => $data['employment_type'],
                'hire_date' => $data['hire_date'],
                'status' => EmployeeStatus::ACTIVE->value,
                'national_id' => $data['national_id'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]);

            event(new EmployeeCreated($employee, $temporaryPassword));

            return $employee->load(['user', 'department', 'jobTitle', 'manager']);
        });
    }
}
