<?php

namespace Modules\Payroll\Services\V1;

use App\Support\Cache\CacheSupport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as ConcretePaginator;
use Illuminate\Support\Facades\Cache;
use Modules\Payroll\Models\PayrollRun;
use Modules\Payroll\Models\Payslip;

class PayrollService
{
    public const CACHE_TAG = 'payroll';

   public function getPaginatedRuns(array $filters): LengthAwarePaginator
    {
        $page = (int) ($filters['page'] ?? 1);
        $perPage = (int) ($filters['per_page'] ?? 15);

        // Standardize key generation by sorting array to avoid duplicate caches for reordered params
        ksort($filters);
        $filterHash = md5(http_build_query($filters));
        $cacheKey = "payroll:runs:list:{$filterHash}:p{$page}:pp{$perPage}";

        $raw = CacheSupport::remember($cacheKey, 1800, function () use ($filters, $perPage) {
            return PayrollRun::query()
                ->when(isset($filters['year']), fn($q) => $q->where('year', $filters['year']))
                ->when(isset($filters['month']), fn($q) => $q->where('month', $filters['month']))
                ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->paginate($perPage)
                ->toArray();
        });

        $items = PayrollRun::hydrate($raw['data']);

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
    public function getPaginatedEmployeePayslips(int $employeeId, int $perPage = 15): LengthAwarePaginator
    {
        $page = request('page', 1);
        $cacheKey = "payroll:payslips:emp:{$employeeId}:p{$page}:pp{$perPage}";

        $raw = CacheSupport::remember($cacheKey, 1800, function () use ($employeeId, $perPage) {
            return Payslip::query()
                ->where('employee_id', $employeeId)
                ->with(['payrollRun', 'deductions'])
                ->orderBy('id', 'desc')
                ->paginate($perPage)
                ->toArray();
        });

        $items = Payslip::hydrate($raw['data']);
        $items->loadMissing(['payrollRun', 'deductions']);

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
public function getPaginatedPayslipsForRun(PayrollRun $payrollRun, int $perPage = 15): LengthAwarePaginator
    {
        $page = (int) request('page', 1);
        $cacheKey = "payroll:run:{$payrollRun->id}:payslips:p{$page}:pp{$perPage}";

        $raw = CacheSupport::remember($cacheKey, 1800, function () use ($payrollRun, $perPage) {
            return $payrollRun->payslips()
                ->with(['employee', 'deductions'])
                ->orderBy('id', 'desc')
                ->paginate($perPage)
                ->toArray();
        });

        $items = Payslip::hydrate($raw['data']);
        $items->loadMissing(['employee', 'deductions']);

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
    public function flushCache(): void
    {
        if (Cache::supportsTags()) {
            Cache::tags([self::CACHE_TAG])->flush();
        } else {
            Cache::flush();
        }
    }
}
