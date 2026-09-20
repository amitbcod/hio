<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tripId = (int) ($argv[1] ?? 174);
$trip = \App\Models\Trip::with(['bookings.lineItems'])->find($tripId);
if (!$trip) { echo "Trip {$tripId} not found\n"; exit(1); }

echo "Trip {$trip->id}: {$trip->title}\n";
foreach ($trip->bookings as $b) {
    echo "Booking id={$b->id}, type={$b->booking_type}, total_amount={$b->total_amount}\n";
    foreach ($b->lineItems as $li) {
        echo " - LineItem id={$li->id}, service_type={$li->service_type}, service_id={$li->service_id}, price={$li->price}\n";
        if ($li->service_type === 'package' || $li->service_type === 'package') {
            $svc = \App\Models\Group::find($li->service_id) ?: \App\Models\Package::find($li->service_id);
            if ($svc) {
                echo "   Service: id={$svc->id}, name={$svc->name}\n";
                echo "   Itinerary: \n" . json_encode($svc->itinerary, JSON_PRETTY_PRINT) . "\n";
            }
        }
    }
}
