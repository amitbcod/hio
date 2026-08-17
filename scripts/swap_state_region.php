<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Accommodation;

$a = Accommodation::find(1);
if ($a) {
    $tmp = $a->state;
    $a->state = $a->region;
    $a->region = $tmp;
    $a->save();
    echo "Swapped\n";
    echo "state=" . $a->state . " region=" . $a->region . "\n";
} else {
    echo "Not found\n";
}
