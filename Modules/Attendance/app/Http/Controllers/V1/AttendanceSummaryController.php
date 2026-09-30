<?php

namespace Modules\Attendance\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Attendance\Http\Requests\AttendanceSummaryRequest;
use Modules\Attendance\Http\Resources\AttendanceResource;
use Modules\Attendance\Http\Resources\AttendanceSummaryResource;
use Modules\Attendance\Models\Attendance;
use Modules\Attendance\Services\V1\AttendanceService;

class AttendanceSummaryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected AttendanceService $queryService
    ) {}

    public function __invoke(AttendanceSummaryRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewSummary', Attendance::class);
       // dd($request->validated());

        $summary = $this->queryService->getMonthlyDepartmentSummary(
            $request->validated()
        );

        return AttendanceSummaryResource::collection($summary);
    }
}
