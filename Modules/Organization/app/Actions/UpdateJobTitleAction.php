<?php

namespace Modules\Organization\Actions;

use App\Support\Cache\CacheSupport;
use Modules\Organization\Models\JobTitle;

class UpdateJobTitleAction
{
    public function execute(JobTitle $jobTitle, array $data): JobTitle
    {
        $oldDepartmentId = $jobTitle->department_id;
        $jobTitle->update($data);

        CacheSupport::forgetMany([
            'job_titles.all',
            "job_titles.department.{$oldDepartmentId}",
            "job_titles.department.{$jobTitle->department_id}",
        ]);

        return $jobTitle;
    }
}
