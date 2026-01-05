<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

// ------------------------------------------------------------------------
// Paypal IPN Class
// ------------------------------------------------------------------------

// Use PayPal on Sandbox or Live
$config['sandbox'] = FALSE; // FALSE for live environment AND TRUE for sandbox

// PayPal Business Email ID
//$config['business'] = 'nick@futurebusinessconcepts.hk';
// 'rutulcom92-facilitator@gmail.com';

//$config['business'] = 'sb-nsgub15143731@business.example.com';
$config['business'] = PAYPAL_BUSSINESS_ACC;
// If (and where) to log ipn to file
$config['paypal_lib_ipn_log_file'] = BASEPATH . 'logs/paypal_ipn.log';
$config['paypal_lib_ipn_log'] = TRUE;

// Where are the buttons located at
$config['paypal_lib_button_path'] = 'buttons';

// What is the default currency?
$config['paypal_lib_currency_code'] = 'EUR';
