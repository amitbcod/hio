<?php

require 'vendor/autoload.php';
require 'bootstrap/app.php';

$app = app();

$count = App\Models\TransportServiceRoutePair::count();
echo "Total route pairs: $count\n";

$byType = App\Models\TransportServiceRoutePair::selectRaw('service_type, COUNT(*) as count')->groupBy('service_type')->get();
echo "By service type:\n";
foreach($byType as $row) {
    echo $row->service_type . ": " . $row->count . "\n";
}

// Show sample data
echo "\nSample data:\n";
$samples = App\Models\TransportServiceRoutePair::limit(5)->get();
foreach($samples as $s) {
    echo "$s->service_type: $s->route_from -> $s->route_to\n";
}
