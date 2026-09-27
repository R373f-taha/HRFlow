<?php

namespace App\Providers;

use App\Mcp\Tools\ClearRoutesAndTestTool;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Organization\Models\Department;
use Modules\Organization\Policies\DepartmentPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Department::class, DepartmentPolicy::class);

        $this->app->singleton(ClearRoutesAndTestTool::class);
    }
}
