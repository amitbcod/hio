<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TravelerAccount;
use App\Models\Trip;
use App\Models\Booking;
use App\Models\BookingLineItem;
use App\Models\TransportBooking;
use App\Models\Transport;
use App\Models\TransportRoute;
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

$trip = Trip::create([
    'traveler_account_id' => $traveler->id,
    'title' => 'Test Group Booking',
    'start_date' => $travelDate,
    'end_date' => $travelDate,
    'status' => 'planned',
]);

$booking = Booking::create([
    'trip_id' => $trip->id,
    'operator_id' => null,
    'total_amount' => 0,
    'status' => 'pending',
    'booking_type' => 'open-group',
]);

$bli = BookingLineItem::create([
    'booking_id' => $booking->id,
    'service_type' => 'package',
    'service_id' => $groupId,
    'quantity' => 1,
    'price' => 0,
    'start_date' => $travelDate,
    'end_date' => $travelDate,
    'status' => 'active',
]);

$pricingService = new PackagePricingService();
$created = [];

// For this test, create two transport bookings matching route IDs 285 and 238 if they exist
$routeIds = [285, 238];
foreach ($routeIds as $rid) {
    $route = TransportRoute::find($rid);
    if (!$route) continue;
    $transport = Transport::find($route->transport_id ?? $route->transport_id ?? ($route->transport->id ?? null));
    if (!$transport) {
        // try via relation
        $transport = $route->transport ?? null;
    }
    $passengers = max(1, $adults);
    $amount = $pricingService->getGroupTransportRouteAmount($route, $passengers, \App\Models\Group::find($groupId), true);
    $tb = TransportBooking::create([
        'booking_reference' => 'TEST-PKG-' . rand(1000,9999),
        'transport_id' => $transport->id ?? null,
        'guest_name' => $traveler->full_name ?? ($traveler->first_name . ' ' . $traveler->last_name),
        'traveler_account_id' => $traveler->id,
        'traveler_relation' => 'self',
        'traveler_first_name' => $traveler->first_name ?? null,
        'traveler_middle_name' => $traveler->middle_name ?? null,
        'traveler_last_name' => $traveler->last_name ?? null,
        'guest_email' => $traveler->email,
        'guest_phone' => $traveler->mobile_phone ?? $traveler->phone ?? null,
        'route_from' => $route->route_from ?? null,
        'route_to' => $route->route_to ?? null,
        'pickup_date' => $travelDate,
        'pickup_time' => $route->start_time ?? null,
        'return_date' => date('Y-m-d', strtotime($travelDate . ' +1 day')),
        'return_time' => $route->end_time ?? null,
        'passengers' => $passengers,
        'adults' => $passengers,
        'children' => 0,
        'booking_status' => 'Pending',
        'total_amount' => $amount,
        'currency' => 'USD',
        'payment_method' => 'COD',
        'source_channel' => 'Package',
        'special_requests' => null,
        'service_type' => 'transport',
        'booked_at' => now(),
        'trip_id' => $trip->id,
        'is_guest' => 0,
    ]);
    $created[] = $tb;
}

if (empty($created)) {
    echo "No transport bookings created (routes not found).\n";
} else {
    foreach ($created as $c) {
        echo "Created TransportBooking id={$c->id} route_from={$c->route_from} route_to={$c->route_to} amount={$c->total_amount}\n";
    }
}

echo "Done. Trip ID: {$trip->id}, Booking ID: {$booking->id}\n";
