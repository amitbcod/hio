<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\BookingWidget;
use App\Models\OperatorProfile;
use App\Models\Operator;

$token = 'ytodKWhsfJGaKV4eIaJelqSAUeJ9lr8WQpILLNL5vkMgQ88F';
$widget = BookingWidget::where('widget_token', $token)->where('is_active', true)->first();
echo 'WIDGET=' . ($widget ? json_encode($widget->toArray()) : 'NULL') . "\n";

$profileByOperatorId = OperatorProfile::where('operator_id', $widget?->operator_id)->first();
echo 'PROFILE_BY_OP=' . ($profileByOperatorId ? json_encode($profileByOperatorId->toArray()) : 'NULL') . "\n";

$operatorByField = Operator::where('operator_id', $widget?->operator_id)->first();
echo 'OPERATOR_BY_FIELD=' . ($operatorByField ? json_encode($operatorByField->toArray()) : 'NULL') . "\n";

$operatorById = Operator::find($widget?->operator_id);
echo 'OPERATOR_BY_ID=' . ($operatorById ? json_encode($operatorById->toArray()) : 'NULL') . "\n";

if ($operatorById) {
    $profileByBusinessId = OperatorProfile::where('business_id', $operatorById->business_id)->first();
    echo 'PROFILE_BY_BUS=' . ($profileByBusinessId ? json_encode($profileByBusinessId->toArray()) : 'NULL') . "\n";
}

$allProfiles = OperatorProfile::limit(5)->get();
echo 'SAMPLE_PROFILES=' . json_encode($allProfiles->toArray()) . "\n";
