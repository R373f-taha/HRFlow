<?php

namespace Modules\Attendance\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Attendance\Actions\RecordCheckInAction;
use Modules\Attendance\Actions\UpdateAttendanceRecordAction;
use Modules\Attendance\Http\Requests\IndexAttendanceRequest;
use Modules\Attendance\Http\Requests\StoreAttendanceRequest;
use Modules\Attendance\Http\Requests\UpdateAttendanceRequest;
use Modules\Attendance\Http\Resources\AttendanceResource;
use Modules\Attendance\Models\Attendance;
use Modules\Attendance\Services\V1\AttendanceService;

class AttendanceController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected AttendanceService $queryService
    ) {}

    public function index(IndexAttendanceRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Attendance::class);

        $attendances = $this->queryService->getFilteredPaginated($request->validated());

        return AttendanceResource::collection($attendances);
    }

    public function store(StoreAttendanceRequest $request, RecordCheckInAction $action): JsonResponse
    {
        $this->authorize('create', Attendance::class);

        $records = $action($request->validated()['records']);

        return response()->json([
            'message' => 'Attendance recorded successfully.',
            'data' => AttendanceResource::collection($records),
        ], 201);
    }

    public function show(Attendance $attendance): AttendanceResource
    {
        $this->authorize('view', $attendance);

        return new AttendanceResource($attendance->load('employee'));
    }

    public function update(
        UpdateAttendanceRequest $request,
        Attendance $attendance,
        UpdateAttendanceRecordAction $action
    ): AttendanceResource {
        $this->authorize('update', $attendance);

        $updated = $action($attendance, $request->validated());

        return new AttendanceResource($updated);
    }
}
