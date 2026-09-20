<?php


namespace Modules\Employees\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Support\Cache\CacheSupport;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Modules\Employees\Models\Employee;
use Modules\Payroll\Transformers\SalaryHistoryResource;

class EmployeeSalaryController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display the salary history for an employee.
     */
    public function history(Employee $employee): AnonymousResourceCollection
    {
        $this->authorize('view', $employee);
        CacheSupport::remember("employees.salary_history.{$employee->id}", 60, function () use ($employee) {
            return $employee->salaryStructures()
                ->orderBy('effective_from', 'desc')
                ->get();
        });

        $history = $employee->salaryStructures()
            ->orderBy('effective_from', 'desc')
            ->get();

        return SalaryHistoryResource::collection($history);
    }
}
