<?php

namespace Modules\Performance\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\Performance\Policies\PerformanceCyclePolicy;
use Nwidart\Modules\Support\ModuleServiceProvider;
use Modules\Performance\Models\PerformanceCycle;
use Modules\Performance\Models\PerformanceReview;

use Modules\Performance\Policies\PerformanceReviewPolicy;

class PerformanceServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Performance';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'performance';

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
     * The policy mappings for the module.
     *
     * @var array<class-string, class-string>
     */
    protected array $policies = [
        PerformanceCycle::class => PerformanceCyclePolicy::class,
        PerformanceReview::class => PerformanceReviewPolicy::class,
    ];

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        parent::boot();

        $this->registerPolicies();
    }

    /**
     * Register the module policies via Gate facade.
     */
    protected function registerPolicies(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
