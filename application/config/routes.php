<?php

defined('BASEPATH') or exit('No direct script access allowed');



/*

| -------------------------------------------------------------------------

| URI ROUTING

| -------------------------------------------------------------------------

| This file lets you re-map URI requests to specific controller functions.

|

| Typically there is a one-to-one relationship between a URL string

| and its corresponding controller class/method. The segments in a

| URL normally follow this pattern:

|

|	example.com/class/method/id/

|

| In some instances, however, you may want to remap this relationship

| so that a different class/function is called than the one

| corresponding to the URL.

|

| Please see the user guide for complete details:

|

|	https://codeigniter.com/user_guide/general/routing.html

|

| -------------------------------------------------------------------------

| RESERVED ROUTES

| -------------------------------------------------------------------------

|

| There are three reserved routes:

|

|	$route['default_controller'] = 'welcome';

|

| This route indicates which controller class should be loaded if the

| URI contains no data. In the above example, the "welcome" class

| would be loaded.

|

|	$route['404_override'] = 'errors/page_missing';

|

| This route will tell the Router which controller/method to use if those

| provided in the URL cannot be matched to a valid route.

|

|	$route['translate_uri_dashes'] = FALSE;

|

| This is not exactly a route, but allows you to automatically route

| controller and method names that contain dashes. '-' isn't a valid

| class or method name character, so it requires translation.

| When you set this option to TRUE, it will replace ALL dashes in the

| controller and method URI segments.

|

| Examples:	my-controller/index	-> my_controller/index

|		my-controller/my-method	-> my_controller/my_method

*/



//echo 'bipin';exit;

$route['default_controller'] = 'HomeController';

// $route['default_controller'] = 'WelcomeController';

$route['404_override'] = 'ErrorController';

$route['translate_uri_dashes'] = false;



$route['category'] = 'ProductsController/productList';

$route['category/(:any)'] = 'ProductsController/productList';

$route['category/(:any)/(:any)'] = 'ProductsController/productList';

$route['category/(:any)/(:any)/(:any)'] = 'ProductsController/productList';

$route['product-detail/(:any)'] = 'ProductsController/productDetails';

$route['review'] = 'ProductReviewController/save_review';





// iframe view

$route['product-detail/prod-view/(:any)'] = 'ProductsController/productQuickDetails';





//User registeration

$route['customer/register'] = 'CustomerController/register';

$route['customer/login'] = 'CustomerController/login';

$route['customer/login_popup'] = 'CustomerController/login_popup';

$route['customer/forgot-password'] = 'CustomerController/forgotPassword';

$route['customer/reset-password/(:any)'] = 'CustomerController/resetPassword';

$route['customer/logout'] = 'CustomerController/logout';





//merchant registeration

$route['merchants/register'] = 'HomeController/register';

$route['merchants/login'] = 'HomeController/login';
$route['merchants/forgot-password'] = 'HomeController/forgotPassword';
$route['merchants/reset-password/(:any)'] = 'HomeController/resetPassword';

//Shops

$route['shops'] = 'ShopController/index';

// AJAX pagination

$route['shops/fetch/(:num)'] = 'ShopController/fetch_shops/$1';

$route['shops/shop_details/(:num)'] = 'ShopController/shop_details/$1';

$route['shops/fetch/(:num)/(:num)'] = 'ShopController/fetch_shops_products/$1/$2';





// Daily deals main page

$route['daily-deals'] = 'DealsController/index';



// Daily deals by category (category id is optional)

$route['daily-deals/category/(:num)'] = 'DealsController/index/$1';



// Flash sale main page

$route['flash-sale'] = 'DealsController/flash_sale';



// Flash sale by category (category id is optional)

$route['flash-sale/category/(:num)'] = 'DealsController/flash_sale/$1';













//My profile

$route['customer/account'] = 'MyProfileController/getProfileDetails';

$route['customer/manage-address'] = 'MyProfileController/getAddressDetails';

$route['customer/my-orders'] = 'MyOrdersController/getOrders';

$route['customer/my-orders/(:num)'] = 'MyOrdersController/getOrders';

$route['customer/my-orders/return-detail/(:num)'] = 'MyOrdersController/returnOrderDetail';

$route['return-order/print/(:num)'] = 'MyOrdersController/printReturnOrder';

$route['customer/special-features'] = 'SpecialFeaturesController/special_features';

$route['customer/upc-catlog-listing'] = 'SpecialFeaturesController/upc_catlog_listing';

$route['customer/upc-catlog/(:any)'] = 'SpecialFeaturesController/upc_catlog_view';

$route['catlog-builder/scanning'] = 'SpecialFeaturesController/catlog_builder_scanning';

$route['catlog-builder/create'] = 'SpecialFeaturesController/create_catlog';

$route['catlog-builder/download-csv'] = 'SpecialFeaturesController/exportCSV';

$route['receipt-order/print/(:any)'] = 'MyOrderReceiptController/printReceiptOrder';
$route['customer/help-desk'] = 'MyProfileController/helpDesk';
$route['customer/messaging'] = 'MyProfileController/messaging';


//Order Track

$route['order-tracking'] = 'MyGuestOrdersController/trackingOrder';

//Others

$route['contact-us'] = 'ContactusController/contactus';

$route['contact-us-post'] = 'ContactusController/contactus_post';

$route['page/(:any)'] = 'HomeController/Pages';

$route['newsletter'] = 'HomeController/newsletterSubscribe';
$route['trending-products/(:num)'] = 'HomeController/trendingProducts/$1';
$route['trending-products'] = 'HomeController/trendingProducts';
$route['newarrival-products'] = 'HomeController/newarrivalProducts';
$route['faqs'] = 'HomeController/faqs';

// Giftcards
$route['giftcards'] = 'Giftcards/index';
$route['giftcards/purchase/(:num)'] = 'Giftcards/purchase/$1';
$route['giftcards/processPurchase'] = 'Giftcards/processPurchase';
$route['giftcards/success/(:any)'] = 'Giftcards/success/$1';
$route['giftcards/failed/(:any)'] = 'Giftcards/failed/$1';
$route['giftcards/mytNotify'] = 'Giftcards/mytNotify';
$route['customer/my-giftcards'] = 'Giftcards/mycards';

// Cart apply/remove giftcard
$route['cart/applyGiftCard'] = 'Giftcards/applyGiftCard';
$route['cart/removeGiftCard'] = 'Giftcards/removeGiftCard';




//Search

$route['searchresult'] = 'SearchController/searchResultPage';



//Wishlist

$route['addtowishlist'] = 'WishlistController/addToWishList';

$route['customer/wishlist'] = 'WishlistController/getMyWishlist';



//Cart

$route['cart'] = 'CartController/index';

$route['cart/index'] = 'CartController/index';



//Cart

$route['checkout'] = 'CheckoutController/index';

$route['checkout/index'] = 'CheckoutController/index';



$route['abandoned_carts_checkout'] = 'CheckoutController/abandoned_carts_checkout';



$route['order/success'] = 'CheckoutController/success';

$route['order/payment-status'] = 'CheckoutController/payment_status';


$route['order/notify'] = 'CheckoutController/notify';

$route['order/failed'] = 'CheckoutController/failed';

$route['order/ipn'] = 'CheckoutController/ipn';
$route['review/form/(:any)'] = 'CustomerController/review_form/$1';
$route['review/save/(:any)'] = 'CustomerController/review_save/$1';





//Guest Orders

$route['guest-order/detail/(:any)'] = 'MyGuestOrdersController/getGuestOrders';

$route['customer/my-guest-orders/return-detail/(:num)'] = 'MyGuestOrdersController/returnOrderDetail';

$route['return-guest-order/print/(:num)'] = 'MyGuestOrdersController/printReturnOrder';



//Pre Launch

$route['pre-launch'] = 'PreLaunchProductsController/productList';



//request payment

$route['request-payment/(:any)'] = 'RequestPaymentController/index';

$route['paymentsuccess'] = 'RequestPaymentController/stripe_success';

$route['paymentfailed'] = 'RequestPaymentController/stripe_failed';



$route['oauth/zumba/login'] = 'Oauth/ZumbaLoginController/login';

$route['oauth/login-with-zumba'] = 'Oauth/ZumbaLoginController/login_with_zumba';

$route['oauth/zumba/confirm_user_email'] = 'Oauth/ZumbaLoginController/confirm_user_email';



$route['zumba/mailjet-signup'] = 'MailjetSignupController';



//Featured products

$route['featured_products'] = 'FeaturedProductsController/productList';

//Trending products

$route['trending_products'] = 'FeaturedProductsController/productList_trending';

//New Arrival products

$route['newarrival_products'] = 'FeaturedProductsController/productList_newarrival';



//Shop All products

$route['shop-all'] = 'ShopAllProductsController/productList';



//testimonials

$route['testimonial'] = 'TestimonialController/testtimonialList';



//Blog

$route['blogs'] = 'BlogController/index';

$route['blogs/(:any)'] = 'BlogController/blogDetails';





$route['convert-images'] = 'ImageConverterController/convert';





$route['e-magazine/(:any)'] = 'MyGuestOrdersController/getEmagazine/$1';

$route['emagazine/validateOtp'] = 'MyGuestOrdersController/validateOtp';


// API
$route['driver_login'] = 'Api/driver_login';
$route['driver_edit'] = 'Api/driver_edit';
$route['get_driver_details'] = 'Api/get_driver_details';

$route['driver_edit_documents'] = 'Api/driver_edit_documents';
$route['driver_delete_documents'] = 'Api/driver_delete_documents';
$route['get_pickup_listing'] = 'Api/get_pickup_listing';
$route['get_delivery_listing'] = 'Api/get_delivery_listing';
$route['get_pickup_order_details'] = 'Api/get_pickup_order_details';
$route['get_delivery_order_details'] = 'Api/get_delivery_order_details';

$route['get_today_route'] = 'Api/get_today_route';
$route['update_order_status'] = 'Api/update_order_status';

$route['pickup_image_upload_details'] = 'Api/pickup_image_upload_details';
$route['pickup_proof_image_upload_details'] = 'Api/pickup_proof_image_upload_details';

$route['delivery_image_upload_details'] = 'Api/delivery_image_upload_details';
$route['update_failed_attempt'] = 'Api/update_failed_attempt';


$route['driver_logout'] = 'Api/driver_logout';


$route['payment/status/(:any)'] = 'HomeController/payment_status_show/$1';
