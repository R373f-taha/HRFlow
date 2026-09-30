<?php

namespace Modules\Attendance\Actions;

use Modules\Attendance\Models\Attendance;
use Modules\Attendance\Services\V1\AttendanceService;

class UpdateAttendanceRecordAction
{
    public function __construct(
        protected AttendanceService $queryService
    ) {}

    public function __invoke(Attendance $attendance, array $data): Attendance
    {
        $attendance->update($data);

        $this->queryService->flushCache();

        return $attendance->fresh();
    }
}
