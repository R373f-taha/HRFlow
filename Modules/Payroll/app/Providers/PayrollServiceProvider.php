<?php

namespace Modules\Payroll\Providers;

use Illuminate\Support\Facades\Gate;
use Nwidart\Modules\Support\ModuleServiceProvider;

use Modules\Payroll\Models\PayrollRun;
use Modules\Payroll\Models\Payslip;
use Modules\Payroll\Models\SalaryStructure;
use Modules\Payroll\Policies\PayrollRunPolicy;
use Modules\Payroll\Policies\PayslipPolicy;
use Modules\Payroll\Policies\SalaryStructurePolicy;

class PayrollServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Payroll';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'payroll';

    /**
     * The policy mappings for the module.
     *
     * @var array<class-string, class-string>
     */
    protected array $policies = [
        SalaryStructure::class => SalaryStructurePolicy::class,
        PayrollRun::class => PayrollRunPolicy::class,
        Payslip::class => PayslipPolicy::class,
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
     * Register module policies with the Gate.
     */
    protected function registerPolicies(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
