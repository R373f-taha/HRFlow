<?php

namespace Modules\Leave\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Modules\Employees\Models\Employee;
use Modules\Leave\Models\LeaveBalance;
use Modules\Leave\Models\LeaveType;
use Modules\Leave\Services\V1\LeaveBalanceService;

class GenerateMonthlyLeaveBalanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(LeaveBalanceService $balanceService): void
    {
        $currentYear = (int) date('Y');
        $leaveTypes = LeaveType::all();

        // Process active employees in chunks to keep memory footprint minimal
        Employee::where('status', 'active')->chunk(100, function ($employees) use ($leaveTypes, $currentYear, $balanceService) {
            DB::transaction(function () use ($employees, $leaveTypes, $currentYear, $balanceService) {
                foreach ($employees as $employee) {
                    foreach ($leaveTypes as $type) {
                        $monthlyAllocation = round($type->annual_days / 12, 2);

                        $balance = LeaveBalance::firstOrCreate(
                            [
                                'employee_id' => $employee->id,
                                'leave_type_id' => $type->id,
                                'year' => $currentYear,
                            ],
                            [
                                'allocated_days' => 0,
                                'used_days' => 0,
                                'remaining_days' => 0,
                            ]
                        );

                        $balance->allocated_days += $monthlyAllocation;
                        $balance->remaining_days += $monthlyAllocation;
                        $balance->save();

                        $balanceService->invalidateCache($employee->id, $currentYear);
                    }
                }
            });
        });
    }
}
