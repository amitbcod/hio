<?php

defined('BASEPATH') OR exit('No direct script access allowed');



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

$route['default_controller'] = 'UserController';

$route['404_override'] = '';

$route['translate_uri_dashes'] = FALSE;



$route['/'] = 'UserController/index';

$route['register'] = 'UserController/register';

$route['congratulations'] = 'UserController/getCongratulationsView';

$route['logout'] = 'UserController/logout';

$route['settings'] = 'UserController/settings';





// email verification

$route['verify-email-address/(:any)'] = 'UserController/setEmailVerificationFlag';

$route['email/verification-successful'] = 'UserController/getEmailVerifySuccessView';

$route['email/verification-unsuccessful'] = 'UserController/getEmailVerifyUnsuccessView';

$route['email/already-verified'] = 'UserController/getEmailAlreadyVerifiedView';



//forgot password

$route['forgot-password'] = 'UserController/forgotPassword';

$route['reset-password'] = 'UserController/resetPassword';

$route['reset-password/(:any)'] = 'UserController/resetPassword';

$route['reset-password-successful'] = 'UserController/getPasswordSuccessView';

$route['reset-password-unsuccessful'] = 'UserController/getPasswordUnsuccessView';

$route['reset-password-invalid-link'] = 'UserController/getPasswordInvalidLinkView';

$route['reset-password-link'] = 'UserController/getPasswordLinkView';



//Dashboard

$route['dashboard'] = 'DashboardController/index';

$route['employee_details'] = 'DashboardController/employee_details';

$route['employee_details/(:any)'] = 'DashboardController/employee_details/$1';



$route['employee_role'] = 'DashboardController/employee_role';

$route['add-employee-role'] = 'DashboardController/createRole';

$route['employee_role/(:num)'] = 'DashboardController/createRole';



//cron outofstock

$route['crons/outofstockcheck/(:any)'] = 'CronOutOfStockController/outofstockcheck/$1';



//Cron

$route['crons/fbcusers_dbcreation'] = 'CronController/fbcusers_dbcreation';

// Cron invoice generate webshop, b2webshop (daily)

$route['crons/webshop/(:any)'] = 'CronController/webshop_invoicing/$1';

$route['crons/b2webshop/(:any)'] = 'CronController/b2webshop_invoicing/$1';



// Cron invoice generate webshop, b2webshop (weekly)

$route['crons/webshop_weekly/(:any)'] = 'CronController/webshop_invoicing_weekly/$1';

$route['crons/b2webshop_weekly/(:any)'] = 'CronController/b2webshop_invoicing_weekly/$1';



// Cron invoice generate webshop, b2webshop (monthly)

$route['crons/webshop_monthly/(:any)'] = 'CronController/webshop_invoicing_monthly/$1';

$route['crons/b2webshop_monthly/(:any)'] = 'CronController/b2webshop_invoicing_monthly/$1';





$route['seller/product/add'] = 'sellerproduct/add';

$route['seller/warehouse'] = 'sellerproduct/index';

$route['seller/product/edit/(:num)'] = 'sellerproduct/editproduct';

$route['seller/product/bulk-add'] = 'sellerproduct/bulkadd';



$route['seller/warehouse/dropship'] = 'sellerproduct/dropship';



$route['seller/get-subcategory'] = 'sellerproduct/getsubcategory';



//Products-Inventory-Overview-Page

$route['seller/product-inventory-adjustments'] = 'Sellerproduct/productInventoryAdjustment';





//Inbound Process

$route['seller/inbound'] = 'InboundController/index';

$route['seller/inbound/add'] = 'InboundController/add';

$route['seller/inbound/import'] = 'InboundController/showImportForm';

$route['seller/inbound/import-post'] = 'InboundController/importPost';

$route['seller/inbound/view/(:num)'] = 'InboundController/editInbound';

$route['seller/inbound/order/detail/(:num)'] = 'InboundController/viewInboundOrder';

$route['seller/inbound/print/(:num)'] = 'InboundController/printOrder';





//B2B Single Import Process

$route['seller/b2webshop-import'] = 'B2BImportController/index';







$route['merchant/subscription'] = 'Subscription/index';







//Webshop

$route['webshop'] = 'WebshopController/webShop';

$route['webshop/themes'] = 'WebshopController/webshopThemes';

$route['webshop/settings'] = 'WebshopController/webshopSettings';

$route['webshop/customize-pages'] = 'WebshopController/webshopCustomizePages';

$route['webshop/pages/add'] = 'WebshopController/CustomizePagesAdd';

$route['webshop/pages/edit/(:num)'] = 'WebshopController/CustomizePagesEdit';



$route['webshop/static-blocks'] = 'WebshopController/webshopStaticBlocks';

$route['webshop/add-static-blocks'] = 'WebshopController/addStaticBlocks';

$route['webshop/edit-static-blocks/(:num)'] = 'WebshopController/editStaticBlocks';

$route['webshop/static-blocks/banner/(:num)'] = 'WebshopController/bannerStaticBlocks';

$route['webshop/static-blocks/homeblock/(:num)'] = 'WebshopController/bannerStaticBlocks';

$route['webshop/static-blocks/menu/(:num)'] = 'WebshopController/menuStaticBlocks';

$route['webshop/add-custom-menu/(:num)'] = 'WebshopController/addCustomMenu';

$route['webshop/edit-custom-menu/(:num)'] = 'WebshopController/editCustomMenu';



$route['webshop/payment'] = 'WebshopController/webshopPayment';

$route['webshop/payment_details/(:num)'] = 'WebshopController/get_gatewayDetails/$1';

$route['webshop/payment_details/(:num)/(:any)'] = 'WebshopController/get_gatewayDetailsStripe/$1/';





$route['webshop/product-blocks'] = 'WebshopController/webshopProductBlocks';

$route['webshop/assign-product-blocks/(:num)'] = 'WebshopController/AssignProductBlocks';



$route['webshop/promo-text-banners'] = 'WebshopController/webshopPromoTextBanners';

$route['webshop/add-promo-text-banners'] = 'WebshopController/addPromoTextBanners';

$route['webshop/edit-promo-text-banners/(:num)'] = 'WebshopController/addPromoTextBanners/$1';



$route['webshop/contact-us-requests'] = 'WebshopController/webshopContactUsRequests';

$route['webshop/edit-contact-us-text'] = 'WebshopController/edit_contactus_text';

$route['contact-us-message'] = 'WebshopController/viewContactUsMessage';



//webshop special pricing

$route['webshop/special-pricing'] = 'WebshopController/special_pricing';

$route['webshop/add-special-pricing'] = 'WebshopController/add_special_pricing';

$route['webshop/edit-special-pricing/(:num)'] = 'WebshopController/edit_special_pricing';

$route['webshop/bulk-add-special-pricing'] = 'WebshopController/bulk_add_special_pricing';



//----------------------------- Start Webshop Discount -------------------------------------

//********** catalogue **********

$route['webshop/catalogue-discounts'] = 'WebshopController/webshopDiscounts';

$route['webshop/catalogue-discounts/add'] = 'WebshopController/add_salesrule_discount_detail';

$route['webshop/catalogue-discounts/edit/(:num)'] = 'WebshopController/add_salesrule_discount_detail';

$route['webshop/discount-product-list/(:num)'] = 'WebshopController/viewCheckedCatProductList';

//************ Product **************

$route['webshop/product-discounts'] = 'WebshopController/webshopDiscounts';

$route['webshop/product-discounts/add'] = 'WebshopController/add_salesrule_discount_detail';

$route['webshop/product-discounts/edit/(:num)'] = 'WebshopController/add_salesrule_discount_detail';

$route['webshop/product-discount-list/(:num)'] = 'WebshopController/viewCategoryProductList';

//************ Coupon code ***********

$route['webshop/coupon-discounts'] = 'WebshopController/webshopDiscounts';

$route['webshop/coupon-discounts/add'] = 'WebshopController/add_salesrule_couponcode_discount_detail';

$route['webshop/coupon-discounts/edit/(:num)'] = 'WebshopController/add_salesrule_couponcode_discount_detail';



//************ Email Coupon ***********

$route['webshop/email-coupon'] = 'WebshopController/webshopDiscounts';

$route['webshop/email-coupon-discounts/add'] = 'WebshopController/add_salesrule_email_discount_detail';

$route['webshop/email-coupon-discounts/edit/(:num)'] = 'WebshopController/add_salesrule_email_discount_detail';

//----------------------------------  End -------------------------------------



//Customer

$route['customertype'] = 'CustomerController/index';

$route['customer-type-details/(:num)'] = 'CustomerController/customer_type_details/$1';

$route['customers'] = 'CustomerController/customers';

$route['add-customer'] = 'CustomerController/add_customer';

$route['customer-details/(:num)'] = 'CustomerController/customer_details/$1';

$route['customertype/sales-rules/(:num)'] = 'CustomerController/salesrule_details/$1';



//Account Manager

$route['account-manager'] = 'ManagerController/index';

$route['account-manager-details/(:num)'] = 'ManagerController/managerAccount_details/$1';



//Shop

$route['product/detail/(:num)/(:num)'] = 'ShopController/productdetail';



//Email Template

$route['email-template'] = 'EmailTemplateController/index';

$route['email-template/details'] = 'EmailTemplateController/template_details/';

$route['email-template/details/(:any)'] = 'EmailTemplateController/template_details/$1';



//Custom Variables

$route['custom-variables'] = 'CustomVariablesController/index';

$route['variable-post'] = 'CustomVariablesController/VariablesPost';

$route['variable-edit'] = 'CustomVariablesController/editCustVariable';



// webshop order

$route['webshop/orders'] = 'WebshopOrdersController/index';

$route['webshop/split-orders'] = 'WebshopOrdersController/index';

$route['webshop/shipped-orders'] = 'WebshopOrdersController/index';

$route['webshop/order/detail/(:num)'] = 'WebshopOrdersController/detail';

$route['webshop/b2b-orders'] = 'B2BOrdersController/index';

$route['webshop/b2b/shipped-orders'] = 'B2BOrdersController/index';

$route['webshop/b2b/order/detail/(:num)'] = 'B2BOrdersController/detail';

$route['webshop/b2b/order/process/(:num)'] = 'B2BOrdersController/process';

$route['webshop/b2b/shipped-order/detail/(:num)'] = 'B2BOrdersController/detail';


$route['webshop/split-order/detail/(:num)'] = 'WebshopOrdersController/detail';

$route['webshop/shipped-order/detail/(:num)'] = 'WebshopOrdersController/detail';

$route['webshop/order/print/(:num)'] = 'WebshopOrdersController/webshopPrintdetails';

$route['webshop/shipped-order/print/(:num)'] = 'WebshopOrdersController/shippedorderprint';

$route['webshop/order/print-label/(:num)'] = 'WebshopOrdersController/orderprintlabel';

$route['webshop/order/print-label-table/(:num)/(:num)'] = 'WebshopOrdersController/orderprintlabel_table';



$route['webshop/supplier-b2b-order/detail/(:num)/(:num)'] = 'WebshopOrdersController/supplierb2borderdetail';





$route['webshop/order/create-shipment/(:num)'] = 'WebshopOrdersController/createShipmentPage';


$route['webshop/b2b/order/create-shipment/(:num)'] = 'B2BOrdersController/createShipmentPage';

$route['webshop/recalculate-order/(:num)'] = 'WebshopOrdersController/recalculate_order';

$route['webshop/recalculate-order-nick/(:num)'] = 'WebshopOrdersController/recalculate_order_nick';



// webshop order returns & refund

$route['webshop/orders/expected-returns'] = 'ReturnOrderController/index';

$route['webshop/orders/returns'] = 'ReturnOrderController/index';

$route['webshop/orders/return-request'] = 'ReturnOrderController/requestOrderList';



$route['webshop/return-request-order/detail/(:num)'] = 'ReturnOrderController/detail';



$route['webshop/orders/refund-request'] = 'ReturnOrderController/requestedrefund';



$route['webshop/refund-request-order/detail/(:num)'] = 'ReturnOrderController/refundorderdetail';



$route['webshop/orders/refund-completed'] = 'ReturnOrderController/completedrefund';

$route['webshop/refund-order/print/(:num)'] = 'ReturnOrderController/return_order_print';

$route['webshop/return-order/print/(:num)'] = 'ReturnOrderController/return_order_print';



// webshop Shipping charges

$route['webshop/shipping-charges'] = 'ShippingChargesController/index';

$route['webshop/edit-shipping-charge/(:num)'] = 'ShippingChargesController/edit_shipping_charge';

$route['webshop/createnew-shipping-charges'] = 'ShippingChargesController/createnew_shipping_charges';

$route['webshop/eu-shipping-charges'] = 'EuShippingChargesController/index';



//Newsletter Subscriber

$route['webshop/newsletter-subscriber'] = 'WebshopController/newsLetter_subscriber';

$route['webshop/edit-newsletter-subscriber-text'] = 'WebshopController/edit_newsLetter_subscriber_text';

$route['webshop/newsletter-subscriber/sync'] = 'NewsletterSubscriberController/syncNewsletterSubscribersToMailjet';



//new

$route['accounting'] = 'AccountingWebshopOrdersController/index';

$route['accounting/webshop'] = 'AccountingWebshopOrdersController/index';

$route['accounting/b2Webshop'] = 'AccountingWebshopOrdersController/b2WebshopList';

$route['accounting/invoicing'] = 'AccountingWebshopOrdersController/invoicingList';

$route['accounting/salesreport'] = 'AccountingWebshopOrdersController/sales_report'; // new



//cancel order

$route['webshop/orders/escalations-request'] = 'ReturnOrderController/requestedEscalations';

$route['webshop/orders/escalations-completed'] = 'ReturnOrderController/completedEscalations';

$route['webshop/escalations-request-order/detail/(:num)'] = 'ReturnOrderController/escalationsRequestOrderDetail';

$route['webshop/escalations-complete-order/detail/(:num)'] = 'ReturnOrderController/escalationsCompleteOrderDetail';



//product reviews

$route['product-reviews'] = 'ProductReviewController/reviewlist';



//report

$route['reports'] = 'ReportController/reportlist';

$route['discount-overview'] = 'ReportController/discountlist';

$route['sales-overview'] = 'ReportController/saleslist';

$route['return-overview'] = 'ReportController/returnlist';

$route['customer-overview'] = 'ReportController/customerList';

$route['publisher-overview'] = 'ReportController/publisherList';



// cancel order menu

$route['webshop/cancel-orders'] = 'WebshopOrdersController/index';

$route['b2b/cancel-orders'] = 'B2BOrdersController/index';





//Vat Settings

$route['vat-setting'] = 'VatController/index';



//Multi currencies Settings

$route['multi_currencies-setting'] = 'Multi_Currencies_Controller/index';

//Multi languages Settings

$route['multi_languages-setting'] = 'Multi_Languages_Controller/index';

$route['languages_translate-setting'] = 'Multi_Languages_Controller/languageTranslate';

$route['variants-translation'] = 'Multi_Languages_Controller/variantsTranslationView';

$route['attribute-translation'] = 'Multi_Languages_Controller/attributesTranslationView';



//Chart Reports

$route['dashboard/webshopreports'] = 'ReportController/webshop_chart_Report';

$route['dashboard/b2webshopreports'] = 'ReportController/b2webshop_chart_Report';



$route['custom/shop1/import-images'] = 'Shop1/ImageMappingController/handle';



//Search-term

$route['webshop/search-term'] = 'Search_Term_Controller/index';



$route['api/send_shipment_confirmation_email'] = 'ApiController/send_shipment_confirmation_email';

$route['api/send_tracking_email'] = 'ApiController/send_tracking_email';

$route['api/process_refund_for_order_with_missing_items'] = 'ApiController/process_refund_for_order_with_missing_items';



$route['api/create_shipment_tracker'] = 'ShipmentTrackerApiController/handle';

$route['webhook/update_shipment_tracker/easypost/(:any)'] = 'ShipmentTrackerWebhookController/easypost_webhook';



//coming soon products

$route['products_notify'] = 'ProductsNotifyController/ProductsList';



//cron for cleanup sales quote data

$route['crons/cleanup_sales_quote_data/(:any)'] = 'CronController/cleanup_sales_quote_data/$1';



// cron for delete login session

$route['crons/login_session/(:any)'] = 'CronController/login_session/$1';



// cron for coming soon items in stock

$route['crons/coming_soon_items_in_stock/(:any)'] = 'CronOutOfStockController/comingsoonitemsinstock/$1';



// wwebshop shipment status

$route['webshop/shipment-status/(:any)'] = 'WebshopOrdersController/ShipmentStatus/$1';



//Category

$route['category'] = 'CategoryController/categoryList';

$route['category/add-category'] = 'CategoryController/addCategory';

$route['category/edit-category/(:any)'] = 'CategoryController/addCategory';

$route['category/submit-category'] = 'CategoryController/submitCategory';



//Admin User

$route['adminuser/user-lists'] = 'AdminuserController/adminLists';

$route['adminuser/add-admin-user'] = 'AdminuserController/add_admin_users';

$route['adminuser/add_admin_user_detail'] = 'AdminuserController/add_admin_user_detail';

$route['adminuser/edit_user/(:any)'] = 'AdminuserController/add_admin_users';



//Attributes

$route['attribute'] = 'AttributeController/attributeList';

$route['attribute/add-attribute'] = 'AttributeController/addAttribute';



// Publishers

$route['merchants'] = 'PublisherController/merchantsList';

$route['merchants/add-merchants'] = 'PublisherController/addMerchants';



// Variants

$route['variants'] = 'VariantsController/varaintList';

$route['variants/add-variants'] = 'VariantsController/addVariants';



// Gift Master

$route['giftMaster'] = 'GiftMasterController/giftMasterList';

// $route['variants/add-variants'] = 'VariantsController/addVariants';



//Admin User Role

$route['adminuserrole/edit-user-role'] = 'AdminuserroleController/editadminuserRole';

$route['adminuserrole/edit-users-role'] = 'AdminuserroleController/editadminusersRole';

$route['adminuserrole/add-admin-user-role'] = 'AdminuserroleController/add_admin_user_role';

$route['adminuserrole/add-admin-user-role/(:any)'] = 'AdminuserroleController/add_admin_user_role';

$route['adminuserrole/delete_user_role/(:any)'] = 'AdminuserroleController/delete_user_role';



//Testimonials

$route['testimonials/testimonial-lists'] = 'TestimonialController/testimonialLists';

$route['testimonials/add-testimonial'] = 'TestimonialController/add_testimonials';

$route['testimonials/add_testimonial_detail'] = 'TestimonialController/add_testimonial_detail';

$route['testimonials/edit_testimonial/(:any)'] = 'TestimonialController/add_testimonials';





// Add-ons

$route['mydocuments'] = 'mydocuments/index';      

$route['mydocuments/add'] = 'mydocuments/add';

$route['mydocuments/insert'] = 'mydocuments/insert';

$route['mydocuments/edit/(:num)'] = 'mydocuments/edit/$1';

$route['mydocuments/update/(:num)'] = 'mydocuments/update/$1';


$route['messaging'] = 'mydocuments/messaging';


$route['productbadges'] = 'ProductBadges/index';      

$route['productbadges/add'] = 'ProductBadges/add';

// MyT Money Addon payment routes
$route['addons/mytNotify'] = 'addons/mytNotify';
$route['addons/mytCallback'] = 'addons/mytCallback';
$route['addons/status/(:any)'] = 'addons/status/$1';


