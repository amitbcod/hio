<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\ClosedGroupBookingController;
use App\Models\Trip;
use App\Models\Booking;

$groupId = (int) ($argv[1] ?? 2);
$pax = (int) ($argv[2] ?? 2);

$data = [
    'group_id' => $groupId,
    'lead_first_name' => 'AdminTest',
    'lead_last_name' => 'User',
    'lead_email' => 'admintest@example.com',
    'lead_phone' => '99999',
    'pax' => $pax,
];

$request = Request::create('/admin/closed-groups/book', 'POST', $data);
$controller = new ClosedGroupBookingController();
$response = $controller->store($request);

if (is_array($response) || $response instanceof Illuminate\Http\JsonResponse) {
    echo "JSON response: ";
    print_r($response->getData(true));
}

// Find most recent trip by title 'Closed Group Booking' or created by this script
$trip = Trip::orderBy('id', 'desc')->first();
if ($trip) {
    echo "Created trip id={$trip->id}, title={$trip->title}\n";
    $book = Booking::where('trip_id', $trip->id)->first();
    if ($book) echo "Booking id={$book->id}, total_amount={$book->total_amount}\n";
}
