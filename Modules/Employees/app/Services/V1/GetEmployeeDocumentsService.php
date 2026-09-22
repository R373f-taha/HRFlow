<?php

namespace Modules\Employees\Services\V1;

use App\Support\Cache\CacheSupport;
use Illuminate\Support\Collection;
use Modules\Employees\Models\Employee;
use Modules\Employees\Resources\EmployeeDocumentResource;

class GetEmployeeDocumentsService
{
    protected const CACHE_TTL = 3600;

    public function execute(Employee $employee): Collection
    {
        $cacheKey = "employees.documents.{$employee->id}";

        return CacheSupport::remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($employee) {
                return $employee->documents()
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        );
    }
/*
    public function execute(Employee $employee): array
{
    $cacheKey = "employees.documents.{$employee->id}";

    return CacheSupport::remember(
        $cacheKey,
        self::CACHE_TTL,
        function () use ($employee) {
            $documents = $employee->documents()
                ->orderBy('created_at', 'desc')
                ->get();

            return EmployeeDocumentResource::collection($documents)
                ->resolve();
        }
    );
}*/
}
