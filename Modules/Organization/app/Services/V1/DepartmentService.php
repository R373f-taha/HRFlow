<?php

namespace Modules\Organization\Services\V1;

use App\Support\Cache\CacheSupport;
use Illuminate\Support\Facades\DB;
use Modules\Employees\Models\Employee;
use Modules\Organization\Models\Department;

class DepartmentService
{
    /**
     * Cache TTL in seconds as specified (60 minutes).
     */
    public const CACHE_TTL = 3600;

    /**
     * Get all hierarchical departments structured as raw cached arrays.
     */
    public function getAllDepartments(): array
    {
        $cacheKey = $this->getListCacheKey();

        return CacheSupport::remember($cacheKey, self::CACHE_TTL, function () {
            return Department::with([
                'manager:id,user_id,employee_number',
                'manager.user:id,name',
                'parent:id,name,code',
                'children:id,name,code,parent_id',
            ])
            ->get()
            ->toArray();
        });
    }

    /**
     * Get a single department by ID formatted as a raw cached array.
     */
    public function getDepartmentById(int $id): ?array
    {
        $cacheKey = $this->getSingleCacheKey($id);

        return CacheSupport::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            $department = Department::with([
                'manager:id,user_id,employee_number',
                'manager.user:id,name',
                'parent:id,name,code',
                'employees:id,user_id,department_id,employee_number,status',
                'employees.user:id,name',
            ])->find($id);

            return $department ? $department->toArray() : null;
        });
    }

    /**
     * Retrieve Eloquent Model instance without eager loading for fast authorization.
     */
    public function findModel(int $id): ?Department
    {
        return Department::find($id);
    }

    /**
     * Create a new department and invalidate cache.
     */
    public function createDepartment(array $data): Department
    {
        $department = Department::create($data);

        $this->invalidateCache();

        return $department;
    }

    /**
     * Update an existing department and invalidate affected cache keys.
     */
public function updateDepartment(Department $department, array $data): Department
    {
        return DB::transaction(function () use ($department, $data) {
            // 1. Update basic department attributes
            $department->update($data);

            // 2. Sync employees if passed in payload
            if (array_key_exists('employees', $data)) {
                // Remove employees previously assigned to this department who were omitted
                // Employee::where('department_id', $department->id)
                //     ->whereNotIn('id', $data['employees'] ?? [])
                //     ->update(['department_id' => null]);

                // Assign selected employees to this department
                if (!empty($data['employees'])) {
                    Employee::whereIn('id', $data['employees'])
                        ->update(['department_id' => $department->id]);
                }
            }

            $this->invalidateCache();

            return $department->fresh(['manager', 'employees']);
        });
    }

    /**
     * Delete department if no restrictions are violated, and invalidate cache.
     */
    public function deleteDepartment(Department $department): bool
    {
        // Business Rule Restriction: Cannot delete department with existing employees or children.
        if ($department->employees()->exists()) {
            abort(422, __('Cannot delete department with assigned employees.'));
        }

        if ($department->children()->exists()) {
            abort(422, __('Cannot delete department that has child departments.'));
        }

        $deleted = $department->delete();

        $this->invalidateCache($department->id);

        return (bool) $deleted;
    }

    /**
     * Invalidate both department list and single item cache.
     */
    public function invalidateCache(?int $id = null): void
    {
        $keys = [$this->getListCacheKey()];

        if ($id !== null) {
            $keys[] = $this->getSingleCacheKey($id);
        }

        CacheSupport::forgetMany($keys);
    }

    public function getSingleCacheKey(int $id): string
    {
        return "department:show:{$id}";
    }

    public function getListCacheKey(): string
    {
        return 'department:list:all';
    }
}
