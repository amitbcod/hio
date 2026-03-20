<?php


use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\BookingController;
use Illuminate\Support\Facades\Route;
require base_path('routes/operator.php');
require base_path('routes/traveler.php');
require base_path('routes/login_fallback.php');
// Admin routes
require base_path('routes/admin.php');
Route::get('/', [HomeController::class, 'index'])->name('frontend.home');
Route::get('/category-list', [HomeController::class, 'categoryList'])->name('frontend.category.list');
Route::get('/activities/{activity}', [HomeController::class, 'showActivity'])->name('frontend.activities.show');
Route::get('/accommodations/{accommodation}', [HomeController::class, 'showAccommodation'])->name('frontend.accommodations.show');

// ── Booking / Cart ────────────────────────────────────────────────────────────
Route::post('/booking/cart/add',    [BookingController::class, 'addToCart'])->name('frontend.booking.cart.add');
Route::get('/booking/cart',         [BookingController::class, 'viewCart'])->name('frontend.booking.cart');
Route::post('/booking/cart/remove', [BookingController::class, 'removeFromCart'])->name('frontend.booking.cart.remove');
Route::get('/booking/checkout',     [BookingController::class, 'checkout'])->name('frontend.booking.checkout');
Route::post('/booking/place-order', [BookingController::class, 'placeOrder'])->name('frontend.booking.place-order');
Route::get('/booking/confirmation/{ref}', [BookingController::class, 'confirmation'])->name('frontend.booking.confirmation');
