<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

    // simple session-based guard (controller checks session before actions)
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('businesses/{business}/approve', [DashboardController::class, 'approveBusiness'])->name('business.approve');
    Route::post('businesses/{business}/reject', [DashboardController::class, 'rejectBusiness'])->name('business.reject');
    Route::post('accommodations/{accommodation}/approve', [DashboardController::class, 'approveAccommodation'])->name('accommodation.approve');
    Route::post('accommodations/{accommodation}/reject', [DashboardController::class, 'rejectAccommodation'])->name('accommodation.reject');

    // Admin role management (create global / business-scoped roles)
    Route::get('roles', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('roles.index');
    Route::post('roles', [\App\Http\Controllers\Admin\RoleController::class, 'store'])->name('roles.store');

    // Admin modules management (CRUD)
    Route::get('modules', [\App\Http\Controllers\Admin\ModuleController::class, 'index'])->name('modules.index');
    Route::get('modules/create', [\App\Http\Controllers\Admin\ModuleController::class, 'create'])->name('modules.create');
    Route::post('modules', [\App\Http\Controllers\Admin\ModuleController::class, 'store'])->name('modules.store');
    Route::get('modules/{module}/edit', [\App\Http\Controllers\Admin\ModuleController::class, 'edit'])->name('modules.edit');
    Route::post('modules/{module}', [\App\Http\Controllers\Admin\ModuleController::class, 'update'])->name('modules.update');
    Route::delete('modules/{module}', [\App\Http\Controllers\Admin\ModuleController::class, 'destroy'])->name('modules.destroy');

    // Admin operators management (CRUD)
    Route::get('operators', [\App\Http\Controllers\Admin\OperatorController::class, 'index'])->name('operators.index');
    Route::get('operators/create', [\App\Http\Controllers\Admin\OperatorController::class, 'create'])->name('operators.create');
    Route::post('operators', [\App\Http\Controllers\Admin\OperatorController::class, 'store'])->name('operators.store');
    Route::get('operators/{operator}/edit', [\App\Http\Controllers\Admin\OperatorController::class, 'edit'])->name('operators.edit');
    Route::post('operators/{operator}', [\App\Http\Controllers\Admin\OperatorController::class, 'update'])->name('operators.update');
    Route::delete('operators/{operator}', [\App\Http\Controllers\Admin\OperatorController::class, 'destroy'])->name('operators.destroy');
});
