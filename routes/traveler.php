<?php

use App\Http\Controllers\Frontend\TravelerAuthController;
use App\Http\Controllers\Frontend\TravelerProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('traveler')->name('traveler.')->group(function () {
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
