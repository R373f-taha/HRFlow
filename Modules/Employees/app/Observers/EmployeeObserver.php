<?php
namespace Modules\Employees\Observers;

use Modules\Employees\Models\Employee;

class EmployeeObserver
{
    /**
     * Handle the Employee "created" event.
     */
    public function created(Employee $employee): void
    {
        // Auto-creates initial salary structure upon hiring an employee
        $employee->salaryStructures()->create([
            'basic_salary' => 0.00,
            'housing_allowance' => 0.00,
            'transport_allowance' => 0.00,
            'other_allowances' => 0.00,
            'effective_from' => $employee->hire_date,
        ]);
    }
}

