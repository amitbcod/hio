<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$refs = array_slice($argv,1);
if (empty($refs)) { echo "Usage: php find_by_ref.php <REF...>\n"; exit(1); }

foreach ($refs as $ref) {
    echo "Searching for: {$ref}\n";
    $ab = \App\Models\AccommodationBooking::where('booking_reference', $ref)->first();
    if ($ab) echo "AccommodationBooking id={$ab->id}, total_amount={$ab->total_amount}, trip_id={$ab->trip_id}\n";
    $act = \App\Models\ActivityBooking::where('booking_reference', $ref)->first();
    if ($act) echo "ActivityBooking id={$act->id}, total_amount={$act->total_amount}, trip_id={$act->trip_id}\n";
    $tb = \App\Models\TransportBooking::where('booking_reference', $ref)->first();
    if ($tb) echo "TransportBooking id={$tb->id}, total_amount={$tb->total_amount}, trip_id={$tb->trip_id}, route_from={$tb->route_from}, route_to={$tb->route_to}\n";
}
echo "Done\n";