<?php
// Boot Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\PackagePricingService;
use App\Models\AccommodationBooking;
use App\Models\ActivityBooking;
use App\Models\TransportBooking;

$ids = array_slice($argv, 1);
if (empty($ids)) {
    echo "Usage: php inspect_bookings.php <id> [<id> ...]\n";
    exit(1);
}

$pricing = new PackagePricingService();

foreach ($ids as $raw) {
    $id = (int) $raw;
    echo "\nInspecting ID: {$id}\n";
    $ab = AccommodationBooking::find($id);
    if ($ab) {
        echo "AccommodationBooking found: id={$ab->id}, trip_id={$ab->trip_id}, total_amount={$ab->total_amount}\n";
        // attempt to recompute via package pricing: find package/group model from trip
        $packageModel = null;
        try {
            $pkgBooking = \App\Models\Booking::where('trip_id', $ab->trip_id)->whereIn('booking_type', ['open-group','package','close-group'])->with('lineItems')->first();
            if ($pkgBooking && $pkgBooking->lineItems && $pkgBooking->lineItems->isNotEmpty()) {
                $svc = $pkgBooking->lineItems->first();
                $serviceId = (int) ($svc->service_id ?? 0);
                if ($serviceId) $packageModel = \App\Models\Package::find($serviceId) ?: \App\Models\Group::find($serviceId);
            }
        } catch (\Exception $e) {}
        $dayEntry = [];
        // try to find itinerary day by matching check_in_date
        $it = $packageModel ? ($packageModel->itinerary ?? []) : [];
        if (!empty($it) && is_array($it)) {
            foreach ($it as $day) {
                if (is_array($day) && !empty($day['accommodation']) && $day['accommodation'] == $ab->accommodation_id) {
                    $dayEntry = $day; break;
                }
            }
        }
        $computed = 0;
        if ($packageModel instanceof \App\Models\Group) {
            try { $computed = $pricing->getAccommodationAmount(\App\Models\Accommodation::find($ab->accommodation_id), is_array($dayEntry)?$dayEntry:[], $packageModel, max(1, (int)$ab->adults), 0, 0); } catch (\Exception $e) {}
        } else {
            try { $computed = $pricing->getAccommodationAmount(\App\Models\Accommodation::find($ab->accommodation_id), is_array($dayEntry)?$dayEntry:[], null, max(1, (int)$ab->adults), 0, 0); } catch (\Exception $e) {}
        }
        echo "Computed accommodation amount: {$computed}\n";
    }

    $act = ActivityBooking::find($id);
    if ($act) {
        echo "ActivityBooking found: id={$act->id}, trip_id={$act->trip_id}, total_amount={$act->total_amount}\n";
        $packageModel = null;
        try {
            $pkgBooking = \App\Models\Booking::where('trip_id', $act->trip_id)->whereIn('booking_type', ['open-group','package','close-group'])->with('lineItems')->first();
            if ($pkgBooking && $pkgBooking->lineItems && $pkgBooking->lineItems->isNotEmpty()) {
                $svc = $pkgBooking->lineItems->first();
                $serviceId = (int) ($svc->service_id ?? 0);
                if ($serviceId) $packageModel = \App\Models\Package::find($serviceId) ?: \App\Models\Group::find($serviceId);
            }
        } catch (\Exception $e) {}
        $dayEntry = [];
        $it = $packageModel ? ($packageModel->itinerary ?? []) : [];
        if (!empty($it) && is_array($it)) {
            foreach ($it as $day) {
                if (is_array($day) && !empty($day['activity']) && $day['activity'] == $act->activity_id) {
                    $dayEntry = $day; break;
                }
            }
        }
        $computed = 0;
        try { $computed = $pricing->getActivityAmount(\App\Models\Activity::find($act->activity_id), is_array($dayEntry)?$dayEntry:[], max(1, (int)$act->adults), $packageModel); } catch (\Exception $e) {}
        echo "Computed activity amount: {$computed}\n";
    }

    $tb = TransportBooking::find($id);
    if ($tb) {
        echo "TransportBooking found: id={$tb->id}, trip_id={$tb->trip_id}, total_amount={$tb->total_amount}, route_from={$tb->route_from}, route_to={$tb->route_to}\n";
        $packageModel = null;
        try {
            $pkgBooking = \App\Models\Booking::where('trip_id', $tb->trip_id)->whereIn('booking_type', ['open-group','package','close-group'])->with('lineItems')->first();
            if ($pkgBooking && $pkgBooking->lineItems && $pkgBooking->lineItems->isNotEmpty()) {
                $svc = $pkgBooking->lineItems->first();
                $serviceId = (int) ($svc->service_id ?? 0);
                if ($serviceId) $packageModel = \App\Models\Package::find($serviceId) ?: \App\Models\Group::find($serviceId);
            }
        } catch (\Exception $e) {}
        // find route model
        $routeModel = null;
        try {
            $transportModel = \App\Models\Transport::with('routes')->find($tb->transport_id);
            if ($transportModel) {
                $routes = $transportModel->routes ?? collect();
                $matched = $routes->first(function($r) use ($tb) {
                    $from = trim((string)($r->route_from ?? ''));
                    $to = trim((string)($r->route_to ?? ''));
                    if ($tb->route_from && $tb->route_to) return strcasecmp($from, $tb->route_from) === 0 && strcasecmp($to, $tb->route_to) === 0;
                    if ($tb->route_from) return strcasecmp($from, $tb->route_from) === 0;
                    if ($tb->route_to) return strcasecmp($to, $tb->route_to) === 0;
                    return false;
                });
                if ($matched) $routeModel = $matched;
                else $routeModel = $routes->first();
            }
        } catch (\Exception $e) {}
        $computed = 0;
        if ($routeModel) {
            try {
                $passengers = max(1, (int) $tb->adults);
                if ($packageModel && $packageModel instanceof \App\Models\Group) {
                    $computed = $pricing->getGroupTransportRouteAmount($routeModel, $passengers, $packageModel, !empty($tb->return_date));
                } else {
                    $computed = $pricing->getTransportRouteAmount($routeModel, $passengers, $packageModel, !empty($tb->return_date));
                }
            } catch (\Exception $e) {}
        }
        echo "Computed transport amount: {$computed}\n";
    }
}

echo "\nDone.\n";
