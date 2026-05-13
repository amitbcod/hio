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
    Route::post('activities/{activity}/approve', [DashboardController::class, 'approveActivity'])->name('activity.approve');
    Route::post('activities/{activity}/reject', [DashboardController::class, 'rejectActivity'])->name('activity.reject');

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

    // Admin travellers management
    Route::get('travellers', [\App\Http\Controllers\Admin\TravelerController::class, 'index'])->name('travellers.index');
    Route::get('travellers/{traveler}/edit', [\App\Http\Controllers\Admin\TravelerController::class, 'edit'])->name('travellers.edit');
    Route::post('travellers/{traveler}', [\App\Http\Controllers\Admin\TravelerController::class, 'update'])->name('travellers.update');
    Route::post('travellers/{traveler}/suspend', [\App\Http\Controllers\Admin\TravelerController::class, 'suspend'])->name('travellers.suspend');
    Route::post('travellers/{traveler}/create-booking', [\App\Http\Controllers\Admin\TravelerController::class, 'createBooking'])->name('travellers.create-booking');

    // Admin trips management
    Route::get('trips', [\App\Http\Controllers\Admin\TripController::class, 'index'])->name('trips.index');
    Route::get('trips/create', [\App\Http\Controllers\Admin\TripController::class, 'create'])->name('trips.create');
    Route::post('trips', [\App\Http\Controllers\Admin\TripController::class, 'store'])->name('trips.store');
    Route::get('trips/{trip}', [\App\Http\Controllers\Admin\TripController::class, 'show'])->name('trips.show');
    Route::get('trips/{trip}/edit', [\App\Http\Controllers\Admin\TripController::class, 'edit'])->name('trips.edit');
    Route::post('trips/{trip}', [\App\Http\Controllers\Admin\TripController::class, 'update'])->name('trips.update');

    // Admin accommodation booking management (superadmin)
    Route::get('accommodation/bookings', [\App\Http\Controllers\Admin\AccommodationBookingController::class, 'index'])->name('accommodation.bookings');
    Route::get('accommodation/bookings/{booking}', [\App\Http\Controllers\Admin\AccommodationBookingController::class, 'show'])->name('accommodation.booking.details');

    // Admin activity booking management (superadmin)
    Route::get('activity/bookings', [\App\Http\Controllers\Admin\ActivityBookingController::class, 'index'])->name('activity.bookings');
    Route::get('activity/bookings/{booking}', [\App\Http\Controllers\Admin\ActivityBookingController::class, 'show'])->name('activity.booking.details');

    // Admin payment transactions management
    Route::get('payment-transactions', [\App\Http\Controllers\Admin\PaymentTransactionController::class, 'index'])->name('payment-transactions.index');
    Route::get('payment-transactions/{transaction}', [\App\Http\Controllers\Admin\PaymentTransactionController::class, 'show'])->name('payment-transactions.show');
    Route::post('payment-transactions/{transaction}/callbacks', [\App\Http\Controllers\Admin\PaymentTransactionController::class, 'getCallbacks'])->name('payment-transactions.callbacks');
});
