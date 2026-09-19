<?php

namespace Modules\Organization\Actions;

use App\Support\Cache\CacheSupport;
use Modules\Organization\Models\JobTitle;

class CreateJobTitleAction
{
    public function execute(array $data): JobTitle
    {
        $jobTitle = JobTitle::create($data);

        CacheSupport::forgetMany([
            'job_titles.all',
            "job_titles.department.{$jobTitle->department_id}",
        ]);

        return $jobTitle;
    }
}
