<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Group;
use App\Services\PackagePricingService;

$groupId = $argv[1] ?? 1;
$adults = isset($argv[2]) ? (int)$argv[2] : 3;
$children = isset($argv[3]) ? (int)$argv[3] : 0;
$infants = isset($argv[4]) ? (int)$argv[4] : 0;

$g = Group::find((int)$groupId);
if (!$g) {
    echo "Group not found: {$groupId}\n";
    exit(2);
}

$svc = new PackagePricingService();
$detail = $svc->calculateGroupTotalDetailed($g, $adults, $children, $infants);
$out = ['group_id' => $g->id, 'group_name' => $g->name, 'adults' => $adults, 'children' => $children, 'infants' => $infants, 'breakdown' => $detail];
$json = json_encode($out, JSON_PRETTY_PRINT);
// Attempt to write to storage/logs for reliable retrieval
$logPath = __DIR__ . '/../storage/logs/price-check-output.json';
@file_put_contents($logPath, $json . PHP_EOL);

// Also echo to stdout
echo $json . PHP_EOL;

return 0;
