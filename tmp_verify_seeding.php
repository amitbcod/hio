<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Connection;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$counts = DB::table('transport_service_route_pairs')
    ->selectRaw('service_type, COUNT(*) as count')
    ->groupBy('service_type')
    ->orderBy('service_type')
    ->get();

$total = DB::table('transport_service_route_pairs')->count();

echo "✓ Route Pair Seeding Complete\n";
echo "================================\n";
echo "Total route pairs seeded: $total\n\n";

echo "By Service Type:\n";
foreach ($counts as $row) {
    echo "  • {$row->service_type}: {$row->count} pairs\n";
}

echo "\nSample Records:\n";
$samples = DB::table('transport_service_route_pairs')->limit(5)->get();
foreach ($samples as $s) {
    echo "  • [{$s->service_type}] {$s->route_from} → {$s->route_to}\n";
}
