<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Frontend\HomeController;
use App\Models\Transport;

$controller = new HomeController();
$transport = Transport::with(['rates','operator'])->find(2);
if (!$transport) { echo "Transport 2 not found\n"; exit; }

$ref = new ReflectionClass($controller);
$method = $ref->getMethod('mapTransport');
$method->setAccessible(true);

$result = $method->invokeArgs($controller, [$transport, false, 'Sir Seewoosagur Ramgoolam Airport', 'Grand Baie']);

echo json_encode($result, JSON_PRETTY_PRINT);
