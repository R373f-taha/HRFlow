<?php

namespace Modules\Attendance\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Modules\Attendance\Models\Attendance;
use Modules\Attendance\Policies\AttendancePolicy;
use Nwidart\Modules\Support\ModuleServiceProvider;

class AttendanceServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Attendance';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'attendance';

    /**
     * The policy mappings for the module.
     *
     * @var array<class-string, class-string>
     */
    protected array $policies = [
        Attendance::class => AttendancePolicy::class,
    ];

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

        $this->registerPolicies();
    }

    /**
     * Register the module's policies.
     */
    protected function registerPolicies(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
