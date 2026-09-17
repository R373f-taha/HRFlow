<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Organization\Http\Controllers\DepartmentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

