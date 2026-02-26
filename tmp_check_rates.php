<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rates = App\Models\ActivityRate::where('activity_id', 1)
    ->orderBy('rate_id')
    ->get(['rate_id','season','rate_specificity','variant_id','valid_from','valid_to']);

var_export($rates->toArray());
