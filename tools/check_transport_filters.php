<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Place;
use App\Models\Transport;
use Illuminate\Support\Str;

$from = 'Sir Seewoosagur Ramgoolam Airport';
$to = 'Grand Baie';

$fromRegion = Place::where('is_active', 1)->where('place_name', trim($from))->value('route_region');
$toRegion = Place::where('is_active', 1)->where('place_name', trim($to))->value('route_region');

echo "filter from: $from -> region: " . var_export($fromRegion, true) . "\n";
echo "filter to:   $to -> region: " . var_export($toRegion, true) . "\n\n";

$transports = Transport::with('routes')->get();

foreach ($transports as $t) {
    echo "Transport id={$t->id}, title={$t->vehicle_name}\n";
    $routes = collect($t->routes)->map(function ($r) { return ['route_from'=>$r->route_from, 'route_to'=>$r->route_to]; })->all();
    echo "  routes:\n";
    foreach ($routes as $r) {
        echo "    - from={$r['route_from']} to={$r['route_to']}\n";
    }

    $matchesFrom = true; $matchesTo = true;
    if (!empty($fromRegion)) {
        $matchesFrom = collect($routes)->contains(function ($route) use ($fromRegion) {
            return Str::lower((string) ($route['route_from'] ?? '')) === Str::lower($fromRegion);
        });
    }
    if (!empty($toRegion)) {
        $matchesTo = collect($routes)->contains(function ($route) use ($toRegion) {
            return Str::lower((string) ($route['route_to'] ?? '')) === Str::lower($toRegion);
        });
    }

    echo "  matchesFrom: ".($matchesFrom? 'YES':'NO')."  matchesTo: ".($matchesTo? 'YES':'NO')."\n\n";
}
