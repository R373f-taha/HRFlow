<?php

namespace Modules\Employees\Services\V1;

use App\Support\Cache\CacheSupport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Modules\Employees\Models\Employee;
use Modules\Employees\Resources\EmployeeResource;

class GetEmployeesService
{
   protected const CACHE_TTL = 3600;

    public function execute(array $filters): array
    {
        $page = $filters['page'] ?? 1;
        $perPage = $filters['per_page'] ?? 15;
        $departmentId = $filters['department_id'] ?? 'all';
        $status = $filters['status'] ?? 'all';
        $search = $filters['search'] ?? 'none';

        $cacheKey = "employees.list.dept_{$departmentId}.status_{$status}.search_{$search}.page_{$page}.per_{$perPage}";

        return CacheSupport::remember($cacheKey, self::CACHE_TTL, function () use ($filters, $perPage) {
            $employees = Employee::query()
                ->with(['user', 'department', 'jobTitle', 'manager'])
                ->when(isset($filters['department_id']), fn ($query) => $query->where('department_id', $filters['department_id']))
                ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
                ->when(isset($filters['search']), function ($query, $search) {
                    $query->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                          ->orWhere('employee_number', 'like', "%{$search}%");
                })
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return EmployeeResource::collection($employees)->response()->getData(true);
        });
    }

    public function getById(int $id): array
    {
        $cacheKey = "employees.detail.{$id}";

        return CacheSupport::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            $employee = Employee::with(['user', 'department', 'jobTitle', 'manager', 'documents'])
                ->findOrFail($id);

            return (new EmployeeResource($employee))->resolve();
        });
    }
}

