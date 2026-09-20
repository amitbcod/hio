<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TransportBooking;
use App\Services\PackagePricingService;

$limit = (int) ($argv[1] ?? 200);
$pricingService = new PackagePricingService();
$rows = TransportBooking::where('source_channel', 'Package')
    ->where('is_guest', 0)
    ->orderBy('id', 'desc')
    ->limit($limit)
    ->with(['transport'])
    ->get();

$issues = [];
foreach ($rows as $tb) {
    $before = (float) $tb->total_amount;
    $computed = null;
    $transportModel = $tb->transport;
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
            // try to detect package/group model
            $packageModel = null;
            try {
                $pkgBooking = \App\Models\Booking::where('trip_id', $tb->trip_id)
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

            if ($packageModel && $packageModel instanceof \App\Models\Group) {
                $computed = $pricingService->getGroupTransportRouteAmount($routeModel, $passengers, $packageModel, !empty($tb->return_date));
            } else {
                $computed = $pricingService->getTransportRouteAmount($routeModel, $passengers, $packageModel, !empty($tb->return_date));
            }
        }
    }

    if ($computed !== null) {
        $computed = (float) round((float) $computed, 2);
        if (abs($before - $computed) > 0.001) {
            $issues[] = [
                'id' => $tb->id,
                'trip_id' => $tb->trip_id,
                'route_from' => $tb->route_from,
                'route_to' => $tb->route_to,
                'saved' => $before,
                'computed' => $computed,
            ];
        }
    }
}

if (empty($issues)) {
    echo "No mismatched recent package transport bookings found (limit={$limit}).\n";
} else {
    echo "Found " . count($issues) . " mismatches:\n";
    foreach ($issues as $i) {
        echo "ID: {$i['id']} Trip: {$i['trip_id']} Route: {$i['route_from']} -> {$i['route_to']} Saved: {$i['saved']} Computed: {$i['computed']}\n";
    }
}

echo "Done.\n";
