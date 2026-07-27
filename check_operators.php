<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking for Operators...\n";

// Check operators table
$operators = DB::table('operators')->limit(5)->get();

if ($operators->count() > 0) {
    echo "Found operators:\n";
    foreach ($operators as $op) {
        echo "  - ID: {$op->id}, Email: {$op->email}, Name: {$op->name}\n";
    }
} else {
    echo "No operators found in database.\n";
}

// Check if there are any transports
$transports = DB::table('transports')->count();
echo "\nTotal transports in DB: $transports\n";

if ($transports > 0) {
    echo "First transport:\n";
    $first = DB::table('transports')->first();
    if ($first) {
        echo "  - ID: {$first->id}, Operator ID: {$first->operator_id}, Service: {$first->service_type}\n";
    }
}
