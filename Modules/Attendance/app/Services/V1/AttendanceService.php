<?php

namespace Modules\Attendance\Services\V1;

use App\Support\Cache\CacheSupport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as ConcretePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Attendance\Models\Attendance;

class AttendanceService
{
    public const CACHE_TAG = 'attendances';
public function getFilteredPaginated(array $filters): LengthAwarePaginator
    {
        $page = (int) ($filters['page'] ?? 1);
        $perPage = (int) ($filters['per_page'] ?? 15);
        $cacheKey = 'attendances:list:' . md5(json_encode($filters) . ":p{$page}:pp{$perPage}");

        $raw = CacheSupport::remember($cacheKey, 1800, function () use ($filters, $perPage) {
            return Attendance::query()
                ->with(['employee:id,department_id'])
                ->when($filters['date'] ?? null, fn ($q, $date) => $q->where('date', $date))
                ->when($filters['from_date'] ?? null, fn ($q, $from) => $q->where('date', '>=', $from))
                ->when($filters['to_date'] ?? null, fn ($q, $to) => $q->where('date', '<=', $to))
                ->when($filters['employee_id'] ?? null, fn ($q, $empId) => $q->where('employee_id', $empId))
                ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
                ->when($filters['department_id'] ?? null, function ($q, $deptId) {
                    $q->whereHas('employee', fn ($e) => $e->where('department_id', $deptId));
                })
                ->orderBy('date', 'desc')
                ->paginate($perPage)
                ->toArray();
        });

        $items = Attendance::hydrate($raw['data']);

        $items->loadMissing(['employee:id,department_id']);

        return resolve(ConcretePaginator::class, [
            'items' => $items,
            'total' => $raw['total'],
            'perPage' => $raw['per_page'],
            'currentPage' => $raw['current_page'],
            'options' => [
                'path' => ConcretePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ],
        ]);
    }

    public function getEmployeeAttendance(int $employeeId, array $filters): LengthAwarePaginator
    {
        $filters['employee_id'] = $employeeId;
        return $this->getFilteredPaginated($filters);
    }

    public function getMonthlyDepartmentSummary(array $data): array
    {
        $departmentId=$data['department_id'];
        $year=$data['year'];
        $month=$data['month'];

        $cacheKey = "attendances:summary:dept:{$departmentId}:y:{$year}:m:{$month}";

        return CacheSupport::remember($cacheKey, 3600, function () use ($departmentId, $year, $month) {
            return DB::table('attendances')
                ->join('employees', 'attendances.employee_id', '=', 'employees.id')
                ->where('employees.department_id', $departmentId)
                ->whereYear('attendances.date', $year)
                ->whereMonth('attendances.date', $month)
                ->select(
                    'attendances.status',
                    DB::raw('COUNT(*) as total_count'),
                    DB::raw('COUNT(DISTINCT attendances.employee_id) as total_employees')
                )
                ->groupBy('attendances.status')
                ->get()
               ->map(fn ($item) => (array) $item)
        ->toArray();
        });
    }

    public function flushCache(): void
    {
        if (Cache::supportsTags()) {
            Cache::tags([self::CACHE_TAG])->flush();
        } else {
            Cache::flush();
        }
    }
}

