<?php
require 'C:\wamp64\www\holidaysio\vendor\autoload.php';
$app = require 'C:\wamp64\www\holidaysio\bootstrap\app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$a = App\Models\Accommodation::with(['rooms','inventory','rates'])->find(1);
echo "Accommodation: " . $a->property_name . PHP_EOL;
foreach ($a->rooms as $r) {
    echo "room=" . $r->id . " name=" . $r->room_name . " status=" . ($r->status ?? '') . " cap=" . $r->capacity . " child=" . $r->children_capacity . " infant=" . $r->infant_capacity . " allot=" . ($r->allotment ?? 'null') . " base=" . ($r->base_price ?? 'null') . PHP_EOL;
}
foreach ($a->inventory as $iv) {
    if ($iv->date === '2026-09-17' || $iv->date === '2026-09-18') {
        echo "inv=" . $iv->room_id . " date=" . $iv->date . " sell=" . $iv->sellable_units . " sold=" . $iv->sold_units . " avail=" . $iv->available_units . " stop=" . ($iv->stop_sell ? '1' : '0') . PHP_EOL;
    }
}
foreach ($a->rates as $r) {
    if ($r->valid_from && $r->valid_to && $r->valid_from <= '2026-09-17' && $r->valid_to >= '2026-09-17') {
        echo "rate=" . $r->id . " room=" . ($r->room_id ?? 'null') . " plan=" . ($r->is_rate_plan ? '1' : '0') . " setting=" . ($r->pricing_setting ?? '') . " meal=" . ($r->meal_plan ?? '') . " final=" . $r->final_rate . " valid=" . $r->valid_from . '-' . $r->valid_to . PHP_EOL;
    }
}
