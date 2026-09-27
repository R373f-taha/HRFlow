<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
use Modules\Leave\Jobs\GenerateMonthlyLeaveBalanceJob;

Schedule::job(new GenerateMonthlyLeaveBalanceJob)->monthlyOn(1, '00:00');
