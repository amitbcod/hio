<?php


use Illuminate\Support\Facades\Route;
require base_path('routes/operator.php');
require base_path('routes/login_fallback.php');
// Admin routes
require base_path('routes/admin.php');
Route::get('/', function () {
    return view('welcome');
});
