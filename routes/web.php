<?php


use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\BookingController;
use App\Http\Controllers\TestController;
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

// Test route
Route::get('/test', [TestController::class, 'test']);

// ── Booking / Cart ────────────────────────────────────────────────────────────
Route::post('/booking/cart/add',    [BookingController::class, 'addToCart'])->name('frontend.booking.cart.add');
Route::get('/booking/cart',         [BookingController::class, 'viewCart'])->name('frontend.booking.cart');
Route::post('/booking/cart/remove', [BookingController::class, 'removeFromCart'])->name('frontend.booking.cart.remove');

// Guest checkout (no auth required)
Route::get('/booking/guest-checkout', [BookingController::class, 'guestCheckout'])->name('frontend.booking.guest-checkout');
Route::post('/booking/create-guest-account', [BookingController::class, 'createGuestAccount'])->name('frontend.booking.create-guest-account');

// Guest order search and OTP send (no auth required)
Route::get('/traveler/guest-order-search', [BookingController::class, 'guestOrderSearch'])->name('frontend.guest-order-search');
Route::post('/traveler/guest-order-search', [BookingController::class, 'sendGuestOrderOtp'])->name('frontend.guest-order-search.send');

// Authenticated checkout
Route::get('/booking/checkout',     [BookingController::class, 'checkout'])->name('frontend.booking.checkout')->middleware('auth:traveler');

// Place order (allows both guest and authenticated)
Route::post('/booking/place-order', [BookingController::class, 'placeOrder'])->name('frontend.booking.place-order');

Route::post('/booking/save-guest', [BookingController::class, 'saveGuest'])->name('frontend.booking.save-guest');
Route::post('/booking/remove-guest', [BookingController::class, 'removeGuest'])->name('frontend.booking.remove-guest');
Route::get('/booking/confirmation/{ref}', [BookingController::class, 'confirmation'])->name('frontend.booking.confirmation');
