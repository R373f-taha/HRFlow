<?php
namespace Modules\Reports\Actions;

use App\Support\Cache\CacheSupport;
use Illuminate\Support\Facades\DB;
use Modules\Employees\Models\Employee;

class GetHeadcountReportAction
{
     protected const CACHE_TTL = 3600;
    public function execute(): array
    {
        return CacheSupport::remember('reports.headcount', self::CACHE_TTL, function () {
        $totalEmployees = Employee::where('status', 'active')->count();

        $byDepartment = DB::table('employees')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->where('employees.status', 'active')
            ->select('departments.name as department', DB::raw('count(employees.id) as count'))
            ->groupBy('departments.id', 'departments.name')
            ->get()
            ->map(fn ($item) => (array) $item)
            ->toArray();

        $byEmploymentType = DB::table('employees')
            ->where('status', 'active')
            ->select('employment_type', DB::raw('count(id) as count'))
            ->groupBy('employment_type')
            ->get()
            ->map(fn ($item) => (array) $item)
            ->toArray();

        return [
            'total_active_employees' => $totalEmployees,
            'by_department' => $byDepartment,
            'by_employment_type' => $byEmploymentType,
        ];
    });}
}
