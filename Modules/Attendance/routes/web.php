<?php

use Illuminate\Support\Facades\Route;
use Modules\Attendance\Http\Controllers\V1\AttendanceController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('attendances', AttendanceController::class)->names('attendance');
});
