<?php

namespace Modules\Organization\Services\V1;

use App\Support\Cache\CacheSupport;
use Illuminate\Support\Collection;
use Modules\Organization\Models\Department;
use Modules\Organization\Models\JobTitle;

class JobTitleService
{
    /**
     * Retrieve all job titles with cached department relation.
     */
    public function getAllCached(): Collection
    {
        $data = CacheSupport::remember('job_titles.all', 3600, function () {
            return JobTitle::with('department')->latest()->get()->toArray();
        });

        return $this->hydrateWithDepartment($data);
    }

    /**
     * Get all job titles belonging to a specific department (cached).
     */
    public function getByDepartmentCached(int $departmentId): Collection
    {
        $data = CacheSupport::remember("job_titles.department.{$departmentId}", 3600, function () use ($departmentId) {
            return JobTitle::with('department')
                ->where('department_id', $departmentId)
                ->latest()
                ->get()
                ->toArray();
        });

        return $this->hydrateWithDepartment($data);
    }

    /**
     * Hydrate collection of JobTitle models and set nested Department relations.
     */
    protected function hydrateWithDepartment(array $data): Collection
    {
        $jobTitles = JobTitle::hydrate($data);
        $dataKeyed = collect($data)->keyBy('id');

        return $jobTitles->map(function (JobTitle $jobTitle) use ($dataKeyed) {
            $rawItem = $dataKeyed->get($jobTitle->id);

            if (isset($rawItem['department'])) {
                $departmentModel = (new Department)->newFromBuilder($rawItem['department']);
                $jobTitle->setRelation('department', $departmentModel);
            }

            return $jobTitle;
        });
    }
}
