<?php
// Boot Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Trip;
use App\Services\PackagePricingService;

$tripId = $argv[1] ?? 161;
$trip = Trip::find($tripId);
if (!$trip) {
    echo "Trip {$tripId} not found\n";
    exit(1);
}

$transportBookings = \App\Models\TransportBooking::where('trip_id', $trip->id)
    ->where('is_guest', 0)
    ->with(['transport', 'pickupDriver', 'returnDriver'])
    ->orderBy('pickup_date', 'asc')
    ->get();

$pricingService = new PackagePricingService();

// Attempt to find package/group booking for this trip so we can pass the proper package/group model
$packageModel = null;
try {
    $pkgBooking = \App\Models\Booking::where('trip_id', $trip->id)
        ->whereIn('booking_type', ['open-group', 'package', 'close-group'])
        ->with(['lineItems'])
        ->first();
    if ($pkgBooking && $pkgBooking->lineItems && $pkgBooking->lineItems->isNotEmpty()) {
        $pkgLine = $pkgBooking->lineItems->first();
        $serviceId = (int) ($pkgLine->service_id ?? 0);
        if ($serviceId) {
            $packageModel = \App\Models\Package::find($serviceId) ?: \App\Models\Group::find($serviceId);
        }
    }
} catch (\Exception $e) {
    // ignore
}

foreach ($transportBookings as $tb) {
    $before = $tb->total_amount;
    $transportModel = $tb->transport;
    $computed = null;
    if ($transportModel) {
        // attempt to find matching route
        $routeModel = null;
        $routeFrom = trim((string) ($tb->route_from ?? ''));
        $routeTo = trim((string) ($tb->route_to ?? ''));
        if ($routeFrom !== '' || $routeTo !== '') {
            $routes = $transportModel->routes ?? collect();
            $matched = $routes->first(function ($r) use ($routeFrom, $routeTo) {
                $from = trim((string) ($r->route_from ?? ''));
                $to = trim((string) ($r->route_to ?? ''));
                if ($routeFrom !== '' && $routeTo !== '') {
                    return strcasecmp($from, $routeFrom) === 0 && strcasecmp($to, $routeTo) === 0;
                }
                if ($routeFrom !== '') return strcasecmp($from, $routeFrom) === 0;
                return $routeTo !== '' ? strcasecmp($to, $routeTo) === 0 : false;
            });
            if ($matched) $routeModel = $matched;
        }
        if (!$routeModel) {
            $routeModel = ($transportModel->routes && $transportModel->routes->isNotEmpty()) ? $transportModel->routes->first() : null;
        }
            if ($routeModel) {
                $passengers = max(1, (int) ($tb->adults ?? $tb->passengers ?? 1));
                if ($packageModel && $packageModel instanceof \App\Models\Group) {
                    $computed = $pricingService->getGroupTransportRouteAmount($routeModel, $passengers, $packageModel, !empty($tb->return_date));
                } else {
                    $computed = $pricingService->getTransportRouteAmount($routeModel, $passengers, $packageModel, !empty($tb->return_date));
                }
            }
    }
    echo "TransportBooking ID: {$tb->id} - Before: {$before}";
    if ($computed !== null) echo " => Computed: {$computed}";
    echo "\n";
        if ($computed !== null) {
            $route = $routeModel;
            echo "  Route ID: " . ($route->id ?? 'n/a') . " From: " . ($route->route_from ?? '') . " To: " . ($route->route_to ?? '') . "\n";
            echo "  Pricing: " . json_encode(is_array($route->pricing ?? null) ? $route->pricing : (is_string($route->pricing ?? null) ? json_decode($route->pricing, true) : $route->pricing)) . "\n";
            echo "  Package/Group present: " . ($packageModel ? ($packageModel->id . ' (' . get_class($packageModel) . ')') : 'none') . "\n";
            if ($packageModel && is_array($packageModel->itinerary ?? null)) {
                $pmIt = $packageModel->itinerary;
                echo "  Itinerary pricing_modes: " . json_encode($pmIt['pricing_modes'] ?? null) . "\n";
                echo "  Itinerary discounts: " . json_encode($pmIt['discounts'] ?? null) . "\n";
            }
        }
}

echo "Done\n";
