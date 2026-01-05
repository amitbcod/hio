<?php 
use App\Controllers\HomeListingController;
use App\Controllers\ProductFiltersController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/webshop/featured_product_listing', HomeListingController::class .':featuredProducts_page');
$app->post('/webshop/newarrival_product_listing', HomeListingController::class .':newArrivals_page');
