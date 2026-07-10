<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Frontend\HomeController;

$request = Request::create('/category-list', 'GET', [
    'category' => 'transport',
    'transport_from' => 'Sir Seewoosagur Ramgoolam Airport',
    'transport_to' => 'Grand Baie',
    'pickup_date' => '2026-07-09',
    'pickup_time' => '00:54',
    'return_date' => '',
    'return_time' => '',
    'passengers' => 1,
]);

$controller = new HomeController();
$view = $controller->categoryList($request);

if (method_exists($view, 'getData')) {
    $data = $view->getData();
    echo "View data keys: " . implode(', ', array_keys($data)) . "\n";
    $results = $data['results'] ?? null;
        echo "Filters: " . json_encode($data['filters'] ?? []) . "\n";
        echo "SearchOptions.transport.froms: " . json_encode($data['searchOptions']['transport']['froms'] ?? []) . "\n";
        echo "SearchOptions.transport.tos: " . json_encode($data['searchOptions']['transport']['tos'] ?? []) . "\n";
    if ($results) {
        echo "Results count: " . $results->total() . "\n";
        foreach ($results as $item) {
            echo "- Item: " . ($item['title'] ?? $item['vehicle_name'] ?? 'unknown') . " (id=" . ($item['id'] ?? '?') . ")\n";
        }
    } else {
        echo "No results key in view data.\n";
    }
} else {
    echo "Returned content is not a View instance.\n";
}
