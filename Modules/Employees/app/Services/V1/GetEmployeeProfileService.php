<?php

namespace Modules\Employees\Services\V1;

use App\Support\Cache\CacheSupport;
use Modules\Auth\Models\User;
use Modules\Employees\Models\Employee;
use Modules\Employees\Resources\EmployeeResource;

class GetEmployeeProfileService
{
    protected const CACHE_TTL = 3600;

/*    public function execute(User $user)//: Employee
    {
        $cacheKey = "employees.profile.user_{$user->id}";

        return CacheSupport::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            return Employee::with(['user', 'department', 'jobTitle', 'manager'])
                ->where('user_id', $user->id)
                ->first();
        });
    }*/
        public function execute(User $user): ?array
    {
        $cacheKey = "employees.profile.user_{$user->id}";

        return CacheSupport::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            $employee = Employee::with(['user', 'department', 'jobTitle', 'manager'])
                ->where('user_id', $user->id)
                ->first();

            if (! $employee) {
                return null;
            }

            return (new EmployeeResource($employee))->resolve();
        });
    }
}
