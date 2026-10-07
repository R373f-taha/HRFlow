<?php

namespace Modules\Payroll\Services\V1;

use App\Support\Cache\CacheSupport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as ConcretePaginator;
use Illuminate\Support\Facades\Cache;
use Modules\Payroll\Models\SalaryStructure;

class SalaryStructureService
{
    public const CACHE_TAG = 'payroll';

    public function getPaginatedSalaryStructures(array $filters): LengthAwarePaginator
    {
        $page = (int) ($filters['page'] ?? 1);
        $perPage = (int) ($filters['per_page'] ?? 15);
        $cacheKey = "payroll:salary_structures:list:p{$page}:pp{$perPage}";

        $raw = CacheSupport::remember($cacheKey, 1800, function () use ($perPage) {
            return SalaryStructure::query()
                ->with('employee')
                ->orderBy('id', 'desc')
                ->paginate($perPage)
                ->toArray();
        });

        $items = SalaryStructure::hydrate($raw['data']);
        $items->loadMissing('employee');

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

    public function createOrUpdateSalaryStructure(array $data): SalaryStructure
    {
        $structure = SalaryStructure::updateOrCreate(
            ['employee_id' => $data['employee_id']],
            $data
        );

        $this->flushCache();

        return $structure;
    }

    public function updateSalaryStructure(SalaryStructure $structure, array $data): SalaryStructure
    {
        $structure->update($data);

        $this->flushCache();

        return $structure;
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
