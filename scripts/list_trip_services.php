<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Trip;
use App\Models\AccommodationBooking;
use App\Models\ActivityBooking;
use App\Models\TransportBooking;

$tripId = (int) ($argv[1] ?? 173);
$trip = Trip::find($tripId);
if (!$trip) {
    echo "Trip {$tripId} not found\n"; exit(1);
}

echo "Trip {$tripId}: {$trip->title}\n";

$acc = AccommodationBooking::where('trip_id', $tripId)->get();
$act = ActivityBooking::where('trip_id', $tripId)->get();
$tb = TransportBooking::where('trip_id', $tripId)->get();

echo "Accommodation bookings (count: " . $acc->count() . "):\n";
foreach ($acc as $a) {
    echo " - id={$a->id}, accommodation_id={$a->accommodation_id}, room_id={$a->room_id}, total_amount={$a->total_amount}\n";
}

echo "Activity bookings (count: " . $act->count() . "):\n";
foreach ($act as $a) {
    echo " - id={$a->id}, activity_id={$a->activity_id}, total_amount={$a->total_amount}\n";
}

echo "Transport bookings (count: " . $tb->count() . "):\n";
foreach ($tb as $t) {
    echo " - id={$t->id}, route_from={$t->route_from}, route_to={$t->route_to}, total_amount={$t->total_amount}\n";
}

echo "Done\n";