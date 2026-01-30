<?php

use Illuminate\Support\Facades\Route;

// Default login route for fallback (if ever needed)
Route::get('login', function () {
    return redirect('/operator/login');
})->name('login');
