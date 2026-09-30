<?php

namespace Modules\Attendance\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Attendance\Http\Resources\AttendanceResource;
use Modules\Attendance\Services\V1\AttendanceService;

class MyAttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $queryService
    ) {}

    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $employeeId = $request->user()->employee?->id;

        abort_if(! $employeeId, 403, 'User is not linked to an employee profile.');

        $attendances = $this->queryService->getEmployeeAttendance($employeeId, $request->all());

        return AttendanceResource::collection($attendances);
    }
}
