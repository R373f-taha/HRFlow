<?php

namespace Modules\Employees\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Support\Facades\Mail;
use Modules\Employees\Emails\WelcomeEmployeeMail;
use Modules\Employees\Events\EmployeeCreated;

class SendWelcomeEmailListener implements ShouldQueue
{
    public function handle(EmployeeCreated $event): void
    {
      $event->employee->loadMissing('user');

        if ($event->employee->user?->email) {
            Mail::to($event->employee->user->email)->send(
                new WelcomeEmployeeMail($event->employee, $event->temporaryPassword)
            );
        }
    }
}
