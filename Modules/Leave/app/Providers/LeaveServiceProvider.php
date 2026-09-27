<?php

namespace Modules\Leave\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Leave\Models\LeaveRequest;
use Modules\Leave\Observers\LeaveRequestObserver;
use Nwidart\Modules\Support\ModuleServiceProvider;

class LeaveServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Leave';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'leave';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Boot the application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Register Model Observers
        LeaveRequest::observe(LeaveRequestObserver::class);
    }

    /**
     * Define module schedules.
     *
     * @param  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
