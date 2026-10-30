<?php

namespace Modules\Reports\Providers;

use Illuminate\Support\Facades\Gate;
use Nwidart\Modules\Support\ModuleServiceProvider;

class ReportsServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Reports';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'reports';

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

        // Register Policy for String/Class authorization checks
        Gate::policy('Modules\Reports\Models\Report', \Modules\Reports\Policies\ReportPolicy::class);
    }
}
