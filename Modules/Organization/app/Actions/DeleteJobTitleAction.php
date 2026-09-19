<?php

namespace Modules\Organization\Actions;

use App\Support\Cache\CacheSupport;
use Modules\Organization\Models\JobTitle;

class DeleteJobTitleAction
{
    public function execute(JobTitle $jobTitle): bool
    {
        $departmentId = $jobTitle->department_id;
        $deleted = $jobTitle->delete();

        if ($deleted) {
            CacheSupport::forgetMany([
                'job_titles.all',
                "job_titles.department.{$departmentId}",
            ]);
        }

        return $deleted;
    }
}
