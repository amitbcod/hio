<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mpo\AuthController;

Route::prefix('mpo')->name('mpo.')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->name('register.post');
    // After registration, step2 route placeholder (mirrors operator)
    Route::get('register/step2-profile', function(){ return view('mpo.registration.step2_placeholder'); })->name('register.step2');

    // Controller verification flows for MPO owners
    Route::post('register/controller/verify/{token}/accept', [\App\Http\Controllers\Mpo\ControllerVerificationController::class, 'accept'])->name('register.controller.verify.accept');
    Route::post('register/controller/verify/{token}/reject', [\App\Http\Controllers\Mpo\ControllerVerificationController::class, 'reject'])->name('register.controller.verify.reject');
    Route::post('register/controller/verify/{token}/claim', [\App\Http\Controllers\Mpo\ControllerVerificationController::class, 'claim'])->name('register.controller.verify.claim');
    Route::get('register/controller/verify/{token}', [\App\Http\Controllers\Mpo\ControllerVerificationController::class, 'show'])->name('register.controller.verify.show');
});
