<?php

use App\Http\Controllers\Mpo\AccountActivationController;
use App\Http\Controllers\Mpo\AuthController;
use App\Http\Controllers\Mpo\ComplianceController;
use App\Http\Controllers\Mpo\ProfileController;
use App\Http\Controllers\Mpo\OperationalModuleController;
use App\Http\Controllers\Mpo\RolePermissionController;
use App\Http\Controllers\Mpo\ServiceBookingController;
use App\Http\Controllers\Mpo\StaffUserController;
use App\Http\Middleware\MpoPortalAuthMiddleware;
use App\Http\Middleware\MpoStaffModuleAccess;
use App\Http\Middleware\MpoStaffOperationalAccess;
use Illuminate\Support\Facades\Route;

Route::prefix('mpo')->name('mpo.')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('users/password/reset/{token}', [StaffUserController::class, 'showPasswordReset'])->name('users.password.reset.form');
    Route::post('users/password/reset', [StaffUserController::class, 'resetPassword'])->name('users.password.reset');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('activate/{administrator}/{token}', [AccountActivationController::class, 'show'])->name('activation.show');
    Route::post('activate/{administrator}/{token}', [AccountActivationController::class, 'update'])->name('activation.update');

    Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->name('register.post');
    // After registration, step2 route placeholder (mirrors operator)
    Route::get('register/step2-profile', function () {
        return view('mpo.registration.step2_placeholder');
    })->name('register.step2');

    Route::middleware(MpoPortalAuthMiddleware::class)->group(function (): void {
        Route::get('profile', [ProfileController::class, 'show'])->middleware(MpoStaffModuleAccess::class.':profile,read')->name('profile');
        Route::post('profile', [ProfileController::class, 'save'])->middleware(MpoStaffModuleAccess::class.':profile,update')->name('profile.save');
        Route::get('compliances', [ComplianceController::class, 'show'])->middleware(MpoStaffModuleAccess::class.':compliance,read')->name('compliances');
        Route::post('compliances', [ComplianceController::class, 'submit'])->middleware(MpoStaffModuleAccess::class.':compliance,update')->name('compliances.save');
        Route::get('compliances/{compliance}/{document}', [ComplianceController::class, 'download'])
            ->middleware(MpoStaffModuleAccess::class.':compliance,read')
            ->whereIn('document', ['proof-of-license', 'insurance-certificate'])
            ->name('compliances.download');
        Route::get('users', [StaffUserController::class, 'index'])->name('users');
        Route::get('users/create', [StaffUserController::class, 'create'])->name('users.create');
        Route::post('users', [StaffUserController::class, 'store'])->name('users.store');
        Route::get('users/role-permissions', [StaffUserController::class, 'rolePermissions'])->name('users.role-permissions');
        Route::get('users/{staffUser}', [StaffUserController::class, 'show'])->whereNumber('staffUser')->name('users.show');
        Route::get('users/{staffUser}/edit', [StaffUserController::class, 'edit'])->whereNumber('staffUser')->name('users.edit');
        Route::put('users/{staffUser}', [StaffUserController::class, 'update'])->whereNumber('staffUser')->name('users.update');
        Route::post('users/{staffUser}/status', [StaffUserController::class, 'updateStatus'])->whereNumber('staffUser')->name('users.status');
        Route::post('users/{staffUser}/reset-password', [StaffUserController::class, 'sendPasswordReset'])->whereNumber('staffUser')->name('users.send-password-reset');
        Route::get('capacity-permissions-and-roles', [RolePermissionController::class, 'index'])->middleware(MpoStaffModuleAccess::class.':capacity-permissions,read')->name('capacity-permissions');
        Route::put('capacity-permissions-and-roles', [RolePermissionController::class, 'update'])->middleware(MpoStaffModuleAccess::class.':capacity-permissions,update')->name('capacity-permissions.update');
        Route::get('collaboration-agreements', [ProfileController::class, 'collaborationAgreements'])->middleware(MpoStaffModuleAccess::class.':collaboration-agreements,read')->name('collaboration-agreements');
        Route::put('collaboration-agreements/contacts/{contactType}', [\App\Http\Controllers\Mpo\CollaborationAgreementController::class, 'updateContact'])
            ->whereIn('contactType', ['management', 'accounting'])
            ->middleware(MpoStaffModuleAccess::class.':collaboration-agreements,update')
            ->name('collaboration-agreements.contacts.update');
        Route::get('collaboration-agreements/documents/{document}', [\App\Http\Controllers\Mpo\CollaborationAgreementController::class, 'download'])
            ->whereNumber('document')
            ->middleware(MpoStaffModuleAccess::class.':collaboration-agreements,read')
            ->name('collaboration-agreements.documents.download');
        Route::get('accounting-commission', [\App\Http\Controllers\Mpo\AccountingCommissionController::class, 'show'])->middleware(MpoStaffModuleAccess::class.':accounting,read')->name('accounting-commission');
        Route::put('accounting-commission', [\App\Http\Controllers\Mpo\AccountingCommissionController::class, 'update'])->middleware(MpoStaffModuleAccess::class.':accounting,update')->name('accounting-commission.update');
        Route::get('accounting-payouts', [\App\Http\Controllers\Mpo\AccountingPayoutController::class, 'show'])->middleware(MpoStaffModuleAccess::class.':accounting,read')->name('accounting-payouts');
        Route::put('accounting-payouts', [\App\Http\Controllers\Mpo\AccountingPayoutController::class, 'update'])->middleware(MpoStaffModuleAccess::class.':accounting,update')->name('accounting-payouts.update');
        Route::get('operations/{module}', [OperationalModuleController::class, 'index'])
            ->whereIn('module', array_keys(config('mpo_staff.registry', [])))
            ->middleware(MpoStaffOperationalAccess::class)
            ->name('operations.module');
        Route::get('operations/bookings/{serviceType}/{booking}', [ServiceBookingController::class, 'show'])
            ->whereIn('serviceType', ['accommodation', 'activity', 'transport'])
            ->whereNumber('booking')
            ->name('operations.booking.show');
        Route::post('operations/bookings/{serviceType}/{booking}/status', [ServiceBookingController::class, 'updateStatus'])
            ->whereIn('serviceType', ['accommodation', 'activity', 'transport'])
            ->whereNumber('booking')
            ->name('operations.booking.status');
        Route::post('operations/bookings/{serviceType}/{booking}/update', [ServiceBookingController::class, 'update'])
            ->whereIn('serviceType', ['accommodation', 'activity', 'transport'])
            ->whereNumber('booking')
            ->name('operations.booking.update');
        Route::get('operations/bookings/activity/{booking}/slots', [ServiceBookingController::class, 'activitySlots'])
            ->whereNumber('booking')
            ->middleware(MpoStaffModuleAccess::class.':activity-bookings,update')
            ->name('operations.booking.activity-slots');
        Route::post('operations/bookings/transport/{booking}/assign', [ServiceBookingController::class, 'assignTransport'])
            ->whereNumber('booking')
            ->name('operations.transport.assign');
    });

    // Controller verification flows for MPO owners
    Route::post('register/controller/verify/{token}/accept', [\App\Http\Controllers\Mpo\ControllerVerificationController::class, 'accept'])->name('register.controller.verify.accept');
    Route::post('register/controller/verify/{token}/reject', [\App\Http\Controllers\Mpo\ControllerVerificationController::class, 'reject'])->name('register.controller.verify.reject');
    Route::post('register/controller/verify/{token}/claim', [\App\Http\Controllers\Mpo\ControllerVerificationController::class, 'claim'])->name('register.controller.verify.claim');
    Route::get('register/controller/verify/{token}', [\App\Http\Controllers\Mpo\ControllerVerificationController::class, 'show'])->name('register.controller.verify.show');
});
