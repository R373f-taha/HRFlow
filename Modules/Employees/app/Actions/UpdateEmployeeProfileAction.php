<?php

namespace Modules\Employees\Actions;

use App\Support\Cache\CacheSupport;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Employees\Models\Employee;

class UpdateEmployeeProfileAction
{
    public function execute(User $user, array $data): Employee
    {
        return DB::transaction(function () use ($user, $data) {
            // 1. Update User level details (name, email)
            $userData = array_filter([
                'name' => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
            ]);

            if (!empty($userData)) {
                $user->update($userData);
            }

            // 2. Update Employee profile details (phone, address)
            $employee = $user->employee;

            $employeeData = array_filter([
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ], fn ($val) => $val !== null);

            if (!empty($employeeData)) {
                $employee->update($employeeData);
            }

            // 3. Purge related caches
            CacheSupport::forget("employees.profile.user_{$user->id}");
            CacheSupport::forget("employees.detail.{$employee->id}");

            return $employee->fresh(['user', 'department', 'jobTitle', 'manager']);
        });
    }
}
