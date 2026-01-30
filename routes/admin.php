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
});
