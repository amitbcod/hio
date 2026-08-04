<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BookingWidget;
use App\Models\OperatorProfile;

$token = 'ytodKWhsfJGaKV4eIaJelqSAUeJ9lr8WQpILLNL5vkMgQ88F';
$widget = BookingWidget::where('widget_token', $token)->where('is_active', true)->first();
if (! $widget) {
    echo "NO_WIDGET\n";
    exit(0);
}
echo "WIDGET=" . json_encode($widget->toArray()) . "\n";
$profile = OperatorProfile::where('operator_id', (string) $widget->operator_id)->first();
if (! $profile) {
    echo "NO_PROFILE\n";
    exit(0);
}
echo "PROFILE=" . json_encode($profile->toArray()) . "\n";
