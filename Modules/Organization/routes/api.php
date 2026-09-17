<?php

use Illuminate\Support\Facades\Route;
use Modules\Organization\Http\Controllers\V1\DepartmentController;
use Modules\Organization\Http\Controllers\V1\OrganizationController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('organizations', OrganizationController::class)->names('organization');
});




// Route::middleware('auth:sanctum')->prefix('v1/departments')->group(function () {
//         Route::get('/{department}',[DepartmentController::class, 'show']
//         )->name('departments.show');
//     });
// use Illuminate\Support\Facades\Route;
// use Modules\Organization\Http\Controllers\V1\Department\GetDepartmentController;
// use Modules\Organization\Http\Controllers\V1\Department\ListDepartmentsController;
// use Modules\Organization\Http\Controllers\V1\Department\CreateDepartmentController;
// use Modules\Organization\Http\Controllers\V1\Department\UpdateDepartmentController;
// use Modules\Organization\Http\Controllers\V1\Department\DeleteDepartmentController;

// Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {

//     // Departments Routes
//     Route::prefix('departments')->group(function () {
//         Route::get('/', ListDepartmentsController::class)->name('departments.index');
//         Route::get('/{id}', GetDepartmentController::class)->name('departments.show');
//         Route::post('/', CreateDepartmentController::class)->name('departments.store');
//         Route::put('/{id}', UpdateDepartmentController::class)->name('departments.update');
//         Route::delete('/{id}', DeleteDepartmentController::class)->name('departments.destroy');
//     });

// });


Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {

    Route::apiResource('departments', DepartmentController::class);

});
