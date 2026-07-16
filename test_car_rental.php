<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

$transport = \App\Models\Transport::find(2);

echo "Transport ID: " . $transport->id . "\n";
echo "Vehicle Name: " . $transport->vehicle_name . "\n";
echo "Car Rental Prices: " . json_encode($transport->car_rental_prices) . "\n";

echo "\nChecking individual prices:\n";
$prices = $transport->car_rental_prices ?? [];
echo "per_hour: " . ($prices['per_hour'] ?? 'NOT SET') . "\n";
echo "per_4h: " . ($prices['per_4h'] ?? 'NOT SET') . "\n";
echo "per_8h: " . ($prices['per_8h'] ?? 'NOT SET') . "\n";
echo "per_12h: " . ($prices['per_12h'] ?? 'NOT SET') . "\n";
echo "per_24h: " . ($prices['per_24h'] ?? 'NOT SET') . "\n";
?>
