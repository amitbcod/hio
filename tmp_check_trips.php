<?php

require __DIR__ . '/bootstrap/app.php';

use App\Models\Trip;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Today is June 8, 2026. Show trips with end_date = June 4, 2026 (4 days ago)
$date = now()->subDays(4)->toDateString();
echo "Today: " . now()->toDateString() . "\n";
echo "Looking for trips with end_date = $date and status = 'Completed'\n\n";

$trips = Trip::whereDate('end_date', $date)
    ->where('status', 'Completed')
    ->get(['id', 'traveler_id', 'end_date', 'status', 'feedback_request_sent_at']);

echo "Found " . count($trips) . " trips matching criteria:\n";
foreach ($trips as $trip) {
    echo "  Trip ID: {$trip->id}, End: {$trip->end_date}, Status: {$trip->status}, Sent At: {$trip->feedback_request_sent_at}\n";
}

// Also show all trips to see date range
echo "\n\nAll trips (recent):\n";
$all = Trip::orderBy('end_date', 'desc')->limit(20)->get(['id', 'end_date', 'status', 'feedback_request_sent_at']);
foreach ($all as $t) {
    echo "  Trip {$t->id}: end_date={$t->end_date}, status={$t->status}, sent_at={$t->feedback_request_sent_at}\n";
}
