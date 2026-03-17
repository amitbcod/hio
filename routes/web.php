<?php


use App\Http\Controllers\Frontend\HomeController;
use Illuminate\Support\Facades\Route;
require base_path('routes/operator.php');
require base_path('routes/login_fallback.php');
// Admin routes
require base_path('routes/admin.php');
Route::get('/', [HomeController::class, 'index'])->name('frontend.home');
Route::get('/category-list', [HomeController::class, 'categoryList'])->name('frontend.category.list');
Route::get('/activities/{activity}', [HomeController::class, 'showActivity'])->name('frontend.activities.show');
Route::get('/accommodations/{accommodation}', [HomeController::class, 'showAccommodation'])->name('frontend.accommodations.show');
