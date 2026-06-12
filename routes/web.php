<?php


use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\BookingController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;
require base_path('routes/operator.php');
require base_path('routes/traveler.php');
require base_path('routes/login_fallback.php');
// Admin routes
require base_path('routes/admin.php');
Route::get('/', [HomeController::class, 'index'])->name('frontend.home');
Route::get('/about-us', [PageController::class, 'aboutUs'])->name('frontend.about-us');
Route::get('/terms-and-conditions', [PageController::class, 'termsAndConditions'])->name('frontend.terms-and-conditions');
Route::get('/privacy-policy', [PageController::class, 'privacyPolicy'])->name('frontend.privacy-policy');
Route::get('/category-list', [HomeController::class, 'categoryList'])->name('frontend.category.list');
Route::get('/activities/{activity}', [HomeController::class, 'showActivity'])->name('frontend.activities.show');
Route::get('/accommodations/{accommodation}', [HomeController::class, 'showAccommodation'])->name('frontend.accommodations.show');

// Test route
Route::get('/test', [TestController::class, 'test']);

// ── Booking / Cart ────────────────────────────────────────────────────────────
Route::post('/booking/cart/add',    [BookingController::class, 'addToCart'])->name('frontend.booking.cart.add');
Route::get('/booking/cart',         [BookingController::class, 'viewCart'])->name('frontend.booking.cart');
Route::post('/booking/cart/remove', [BookingController::class, 'removeFromCart'])->name('frontend.booking.cart.remove');
Route::get('/booking/shared/{token}', [BookingController::class, 'viewSharedCart'])->name('frontend.booking.shared');
Route::get('/booking/shared/init/{token}', [BookingController::class, 'initSharedCartBuilder'])->name('frontend.booking.shared.init');

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
Route::match(['get', 'post'], '/booking/payment/callback', [BookingController::class, 'paymentCallback'])->name('frontend.booking.payment.callback');
Route::get('/booking/payment/return', [BookingController::class, 'paymentReturn'])->name('frontend.booking.payment.return');

Route::post('/booking/save-guest', [BookingController::class, 'saveGuest'])->name('frontend.booking.save-guest');
Route::post('/booking/remove-guest', [BookingController::class, 'removeGuest'])->name('frontend.booking.remove-guest');
Route::get('/booking/confirmation/{ref}', [BookingController::class, 'confirmation'])->name('frontend.booking.confirmation');

// Feedback routes
use App\Http\Controllers\Frontend\FeedbackController;
Route::get('/feedback/trip/{trip}', [FeedbackController::class, 'show'])->name('frontend.feedback.show');
Route::post('/feedback/trip/{trip}', [FeedbackController::class, 'submit'])->name('frontend.feedback.submit');
Route::get('/feedback/send/{trip}', [FeedbackController::class, 'sendRequest'])->name('frontend.feedback.send');
