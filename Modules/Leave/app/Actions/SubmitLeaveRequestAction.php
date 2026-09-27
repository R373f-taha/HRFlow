<?php

namespace Modules\Leave\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Modules\Leave\Events\LeaveRequestSubmitted;
use Modules\Leave\Models\LeaveRequest;
use Modules\Leave\Services\V1\LeaveRequestService;

class SubmitLeaveRequestAction
{
    public function __construct(private LeaveRequestService $leaveRequestService) {}

    public function execute(int $employeeId, array $data, ?UploadedFile $document = null): LeaveRequest
    {

        return DB::transaction(function () use ($employeeId, $data, $document) {

            $documentPath = null;

            if ($document) {// Store medical document in private storage if present
                $documentPath = $document->store('leave_documents', 'private');
            }

            $leaveRequest = LeaveRequest::create([
                'employee_id' => $employeeId,
                'leave_type_id' => $data['leave_type_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => 'pending',
                'document_path' => $documentPath,
            ]);

            $this->leaveRequestService->invalidateCache($employeeId);

            event(new LeaveRequestSubmitted($leaveRequest));

            return $leaveRequest;
        });
    }
}
