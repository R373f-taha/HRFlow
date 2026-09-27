<?php

namespace Modules\Organization\Services\V1;

use App\Support\Cache\CacheSupport;
use Illuminate\Database\Eloquent\Collection;
use Modules\Organization\Models\JobTitle;

class JobTitleService
{
    /**
     * Retrieve all job titles with cached department relation.
     */
    public function getAllCached(): Collection
    {
        return CacheSupport::remember('job_titles.all', 3600, function () {
            return JobTitle::with('department')
                ->latest()
                ->get();
        });
    }

    public function getByDepartmentCached(int $departmentId): \Illuminate\Database\Eloquent\Collection
    {
        return CacheSupport::remember("job_titles.department.{$departmentId}", 3600, function () use ($departmentId) {
            return JobTitle::with('department')
                ->where('department_id', $departmentId)
                ->latest()
                ->get();
        });
    }
}
