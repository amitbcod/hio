<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TravelerAccount;
use App\Services\PackagePricingService;

$email = $argv[1] ?? 'amit29592@gmail.com';
$groupId = (int) ($argv[2] ?? 2);
$travelDate = $argv[3] ?? '2026-09-21';
$adults = (int) ($argv[4] ?? 2);

$traveler = TravelerAccount::where('email', $email)->first();
if (!$traveler) {
    echo "Traveler not found: {$email}\n";
    exit(1);
}

// Log in traveler in guard
Auth::guard('traveler')->loginUsingId($traveler->id);

// Build a cart containing a package item for the group
$cartKey = 'pkg_test_' . time();
$cart = [
    $cartKey => [
        'type' => 'package',
        'source_type' => 'group',
        'package_id' => $groupId,
        'group_id' => $groupId,
        'package_name' => 'Test Group',
        'check_in' => $travelDate,
        'check_out' => $travelDate,
        'adults' => $adults,
        'children' => 0,
        'infants' => 0,
        'currency' => 'USD',
        'net_amount' => 1000,
        'cart_key' => $cartKey,
    ]
];

// Put cart into session
session()->put('booking_cart', $cart);

// Build request payload for placeOrder
$post = [
    'guest_email' => $traveler->email,
    'guest_phone' => $traveler->mobile_phone ?? $traveler->phone ?? '0000',
    'payment_method' => 'cod',
    'guests' => [
        $cartKey => [
            [
                'first_name' => $traveler->first_name ?? 'Test',
                'middle_name' => '',
                'last_name' => $traveler->last_name ?? 'User',
                'dob' => optional($traveler->profile)->date_of_birth?->format('Y-m-d') ?? '1990-01-01',
                'gender' => 'male'
            ]
        ]
    ]
];

$request = Request::create('/place-order', 'POST', $post);
// attach session instance
$request->setLaravelSession(session());

$controller = app()->make(\App\Http\Controllers\Frontend\BookingController::class);
try {
    $response = $controller->placeOrder($request);
    echo "placeOrder response type: " . get_class($response) . "\n";
} catch (Exception $e) {
    echo "Exception during placeOrder: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit(1);
}

// Find the most recent trip for this traveler
$trip = \App\Models\Trip::where('traveler_account_id', $traveler->id)->orderBy('id', 'desc')->first();
if (!$trip) {
    echo "No trip found\n";
    exit(0);
}

$transportBookings = \App\Models\TransportBooking::where('trip_id', $trip->id)->get();
foreach ($transportBookings as $tb) {
    echo "TransportBooking id={$tb->id} route_from={$tb->route_from} route_to={$tb->route_to} total_amount={$tb->total_amount}\n";
}

echo "Done. Trip={$trip->id}\n";
