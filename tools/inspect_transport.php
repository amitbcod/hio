<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Place;
use App\Models\Transport;

$from = 'Sir Seewoosagur Ramgoolam Airport';
$to = 'Grand Baie';

$fromRegion = Place::where('is_active', 1)->where('place_name', trim($from))->value('route_region');
$toRegion = Place::where('is_active', 1)->where('place_name', trim($to))->value('route_region');

echo "fromRegion:" . var_export($fromRegion, true) . "\n";
echo "toRegion:" . var_export($toRegion, true) . "\n";

$t = Transport::with('routes')->find(2);
if (!$t) {
    echo "Transport id 2 not found\n";
    exit(0);
}

$routes = [];
foreach ($t->routes as $r) {
    $routes[] = [
        'route_from' => $r->route_from,
        'route_to' => $r->route_to,
        'price' => $r->default_price,
    ];
}

echo json_encode(['routes' => $routes], JSON_PRETTY_PRINT) . "\n";
