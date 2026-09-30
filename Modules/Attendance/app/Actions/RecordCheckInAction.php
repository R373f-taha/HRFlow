<?php

namespace Modules\Attendance\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Attendance\Models\Attendance;
use Modules\Attendance\Services\V1\AttendanceService;

class RecordCheckInAction
{
    public function __construct(
        protected AttendanceService $queryService
    ) {}
    /**
     * Record check-in and check-out for multiple employees.
     * enter multiple records in the request body, each record should contain employee_id, date, check_in, check_out, status and note.
     * if empolyee has a previous record for the same date, it will be updated with the new check_in and check_out times.
     * but if the employee has no previous record for the same date, a new record will be created.
     * @param array $records
     * @return mixed|Collection
     */
    public function __invoke(array $records): Collection
    {
        return DB::transaction(function () use ($records) {
            $insertedRecords = collect();

            foreach ($records as $record) {
                $attendance = Attendance::updateOrCreate(//if the record exists, update it; otherwise, create a new one
                    [
                        'employee_id' => $record['employee_id'],
                        'date' => $record['date'],
                    ],
                    [
                        'check_in' => $record['check_in'] ?? null,
                        'check_out' => $record['check_out'] ?? null,
                        'status' => $record['status'],
                        'note' => $record['note'] ?? null,
                    ]
                );

                $insertedRecords->push($attendance);
            }

            $this->queryService->flushCache();

            return $insertedRecords;
        });
    }
}
