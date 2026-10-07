<?php

namespace Modules\Payroll\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use Modules\Payroll\Events\PayrollFinalizedEvent;
use Modules\Payroll\Notifications\PayrollFinalizedNotification;

class SendPayrollFinalizedNotificationsListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PayrollFinalizedEvent $event): void
    {
        $event->payrollRun->load(['payslips.employee.user']);

        foreach ($event->payrollRun->payslips as $payslip) {
            $user = $payslip->employee?->user;

            if ($user) {
                $user->notify(new PayrollFinalizedNotification($payslip));
            }
        }
    }
}
