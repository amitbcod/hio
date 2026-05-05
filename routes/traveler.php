<?php

use App\Http\Controllers\Frontend\TravelerAuthController;
use App\Http\Controllers\Frontend\TravelerProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('traveler')->name('traveler.')->group(function () {
    // Guest trips (no auth required, uses OTP)
    Route::get('/guest-trips/{otp}', [\App\Http\Controllers\Frontend\GuestTripController::class, 'show'])->name('guest-trip.show');
    Route::get('/guest-trips/{otp}/trips/{trip}', [\App\Http\Controllers\Frontend\GuestTripController::class, 'showTrip'])->name('guest-trip.detail');
    Route::post('/guest-trips/{otp}/verify', [\App\Http\Controllers\Frontend\GuestTripController::class, 'verify'])->name('guest-trip.verify');
    Route::get('/guest-trips/logout', [\App\Http\Controllers\Frontend\GuestTripController::class, 'logout'])->name('guest-trip.logout');

    Route::get('/guest-trips/{otp}/trips/{trip}/booking/{booking}/manage-guests', [\App\Http\Controllers\Frontend\GuestTripController::class, 'manageGuests'])->name('guest-trip.trip.booking.manage-guests');
    Route::post('/guest-trips/{otp}/trips/{trip}/booking/{booking}/manage-guests', [\App\Http\Controllers\Frontend\GuestTripController::class, 'updateGuests'])->name('guest-trip.trip.booking.update-guests');
    Route::post('/guest-trips/{otp}/trips/{trip}/add-service', [\App\Http\Controllers\Frontend\GuestTripController::class, 'confirmAddService'])->name('guest-trip.trip.add-service');
    Route::get('/guest-trips/{otp}/trips/{trip}/booking/{booking}/download-voucher/{guest?}', [\App\Http\Controllers\Frontend\GuestTripController::class, 'downloadVoucher'])->name('guest-trip.trip.booking.download-voucher');

    Route::middleware('guest:traveler')->group(function () {
        Route::get('/register', [TravelerAuthController::class, 'showRegisterForm'])->name('register');
        Route::post('/register', [TravelerAuthController::class, 'register'])->name('register.store');

        Route::get('/login', [TravelerAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [TravelerAuthController::class, 'login'])->name('login.store');
    });

    Route::middleware('auth:traveler')->group(function () {
        Route::get('/profile', [TravelerProfileController::class, 'showProfile'])->name('profile');
        Route::post('/profile', [TravelerProfileController::class, 'updateProfile'])->name('profile.update');
        Route::get('/settings', [TravelerProfileController::class, 'showSettings'])->name('settings');
        Route::post('/settings', [TravelerProfileController::class, 'updateSettings'])->name('settings.update');
        Route::post('/settings/reset-password', [TravelerProfileController::class, 'requestPasswordReset'])->name('settings.reset-password');
        Route::post('/settings/toggle-suspension', [TravelerProfileController::class, 'toggleAccountSuspension'])->name('settings.toggle-suspension');

        // Trips
        Route::get('/trips', [\App\Http\Controllers\Frontend\TripController::class, 'index'])->name('trips');
        Route::get('/trips/{trip}', [\App\Http\Controllers\Frontend\TripController::class, 'show'])->name('trip.detail');
        Route::get('/trips/{trip}/booking/{booking}/manage-guests', [\App\Http\Controllers\Frontend\TripController::class, 'manageGuests'])->name('trip.booking.manage-guests');
        Route::post('/trips/{trip}/booking/{booking}/manage-guests', [\App\Http\Controllers\Frontend\TripController::class, 'updateGuests'])->name('trip.booking.update-guests');
        Route::get('/trips/{trip}/booking/{booking}/download-voucher/{guest?}', [\App\Http\Controllers\Frontend\TripController::class, 'downloadVoucher'])->name('trip.booking.download-voucher');
        Route::post('/trips/{trip}/add-service', [\App\Http\Controllers\Frontend\TripManagementController::class, 'confirmAddService'])->name('trip.add-service');

        Route::post('/logout', [TravelerAuthController::class, 'logout'])->name('logout');
    });
});
