<?php

namespace Modules\Employees\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Employees\Models\Employee;

class EmployeeCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Employee $employee,
        public string $temporaryPassword
    ) {}


}
