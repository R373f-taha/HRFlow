<?php

namespace Modules\Payroll\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Payroll\Events\PayrollFinalizedEvent;
use Modules\Payroll\Listeners\SendPayrollFinalizedNotificationsListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */protected $listen = [
        PayrollFinalizedEvent::class => [
            SendPayrollFinalizedNotificationsListener::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
