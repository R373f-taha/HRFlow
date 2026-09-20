<?php

namespace Modules\Employees\Actions;

use Illuminate\Support\Facades\Cache;
use Modules\Employees\Models\Employee;

class UpdateEmployeeAction
{
    public function execute(Employee $employee, array $data): Employee
    {
        $employee->update($data);


        if (isset($data['name']) || isset($data['email'])) {
            $employee->user->update(
                array_filter([
                    'name'  => $data['name'] ?? null,
                    'email' => $data['email'] ?? null,
                ])
            );
        }


        Cache::forget("employees.detail.{$employee->id}");
        
        return $employee->fresh(['user', 'department', 'jobTitle', 'manager']);
    }
}
