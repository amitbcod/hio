<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TransportRoute;
use App\Models\Group;
use App\Services\PackagePricingService;

$route = TransportRoute::find(238);
$group = Group::find(2);
$svc = new PackagePricingService();

echo "Route pricing: \n" . json_encode(is_array($route->pricing ?? null) ? $route->pricing : (is_string($route->pricing ?? null) ? json_decode($route->pricing, true) : $route->pricing), JSON_PRETTY_PRINT) . "\n";

$res1 = $svc->getGroupTransportRouteAmount($route, 2, $group, false);
$res2 = $svc->getGroupTransportRouteAmount($route, 2, $group, true);
$res3 = $svc->getTransportRouteAmount($route, 2, $group, false);
$res4 = $svc->getTransportRouteAmount($route, 2, $group, true);

echo "getGroupTransportRouteAmount wantReturn=false: {$res1}\n";
echo "getGroupTransportRouteAmount wantReturn=true: {$res2}\n";
echo "getTransportRouteAmount wantReturn=false: {$res3}\n";
echo "getTransportRouteAmount wantReturn=true: {$res4}\n";
