<?php

use App\Http\Controllers\Agent\AuthController;
use App\Http\Controllers\Agent\SetupController;
use App\Http\Middleware\AgentAccountNotSuspended;
use App\Http\Middleware\AgentModuleAccess;
use Illuminate\Support\Facades\Route;

Route::prefix('agent')->name('agent.')->group(function (): void {
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->name('register.store');
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.store');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth:agent,agent_staff', AgentAccountNotSuspended::class])->group(function (): void {
        Route::get('/', [SetupController::class, 'dashboard'])->name('dashboard');

        Route::get('setup/profile', [SetupController::class, 'profile'])->middleware(AgentModuleAccess::class.':profile,read')->name('profile');
        Route::post('setup/profile', [SetupController::class, 'saveProfile'])->middleware(AgentModuleAccess::class.':profile,update')->name('profile.save');
        Route::get('setup/compliance', [SetupController::class, 'compliance'])->middleware(AgentModuleAccess::class.':compliance,read')->name('compliance');
        Route::post('setup/compliance', [SetupController::class, 'saveCompliance'])->middleware(AgentModuleAccess::class.':compliance,update')->name('compliance.save');
        Route::get('setup/compliance/{document}/download', [SetupController::class, 'downloadCompliance'])->middleware(AgentModuleAccess::class.':compliance,read')->name('compliance.download');
        Route::get('setup/users', [SetupController::class, 'users'])->middleware(AgentModuleAccess::class.':users,read')->name('users');
        Route::post('setup/users', [SetupController::class, 'saveUser'])->middleware(AgentModuleAccess::class.':users,create')->name('users.save');
        Route::post('setup/users/{user}/reset-password', [SetupController::class, 'resetUserPassword'])->middleware(AgentModuleAccess::class.':users,update')->name('users.reset-password');
        Route::post('setup/users/{user}/status', [SetupController::class, 'updateUserStatus'])->middleware(AgentModuleAccess::class.':users,update')->name('users.status');
        Route::get('setup/operations', [SetupController::class, 'operations'])->middleware(AgentModuleAccess::class.':operations,read')->name('operations');
        Route::post('setup/operations', [SetupController::class, 'saveOperations'])->middleware(AgentModuleAccess::class.':operations,update')->name('operations.save');
    });
});
