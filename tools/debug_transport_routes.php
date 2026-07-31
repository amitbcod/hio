<?php
require __DIR__ . '/..\vendor\autoload.php';
$app = require_once __DIR__ . '/..\bootstrap\app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TransportRoute;

$rows = TransportRoute::query()
    ->where(function($q) {
        $q->where('route_from', 'LIKE', '%Airport%')
          ->orWhere('route_to', 'LIKE', '%Airport%')
          ->orWhere('route_from', 'LIKE', '%North%')
          ->orWhere('route_to', 'LIKE', '%North%');
    })->get();

echo "Found: " . $rows->count() . " routes\n";
foreach ($rows as $r) {
    echo "ID: {$r->id} route_id: {$r->route_id}\n";
    echo "from: {$r->route_from} | to: {$r->route_to}\n";
    echo "pricing: " . json_encode($r->pricing) . "\n\n";
}

// Also show some transports with routes_pricing
use App\Models\Transport;
$trans = Transport::with('routes')->take(20)->get();
foreach ($trans as $t) {
    foreach ($t->routes as $rt) {
        if (stripos($rt->route_from, 'Airport') !== false || stripos($rt->route_to, 'Airport') !== false || stripos($rt->route_from, 'North') !== false || stripos($rt->route_to, 'North') !== false) {
            echo "Transport {$t->id} {$t->vehicle_name}: route {$rt->route_from} -> {$rt->route_to} pricing=" . json_encode($rt->pricing) . "\n";
        }
    }
}
