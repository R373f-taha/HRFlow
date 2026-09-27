<?php

namespace Modules\Leave\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Leave\Events\LeaveRequestApproved;
use Modules\Leave\Events\LeaveRequestSubmitted;
use Modules\Leave\Listeners\SendLeaveRequestNotificationToManager;
use Modules\Leave\Listeners\UpdateBalanceAndNotifyOnApproval;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        LeaveRequestSubmitted::class => [
            SendLeaveRequestNotificationToManager::class,
        ],
        LeaveRequestApproved::class => [
            UpdateBalanceAndNotifyOnApproval::class,
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
