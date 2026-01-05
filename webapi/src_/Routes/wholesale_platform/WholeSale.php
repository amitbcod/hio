<?php

use App\Controllers\WholesalePlatform\ExportCSVWholesaleController;
use App\Controllers\WholesalePlatform\PlaceWholesaleOrderController;
use App\Controllers\WholesalePlatform\VirtualInventoryController;

/** @var $app */
$app->post('/wholesale_platform/place_order', PlaceWholesaleOrderController::class);
$app->get('/wholesale_platform/virtual_inventory', VirtualInventoryController::class);
$app->get('/wholesale_platform/ExportCSVWholesale/{shopcode}', ExportCSVWholesaleController::class);
