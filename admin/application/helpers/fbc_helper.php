<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');


function dd($variable){
	echo "<hr><pre>";
	var_dump($variable);
	echo "</pre><hr>";
	exit;
}

function fbc_switch_db_dynamic($name_db)
{

	$CI =& get_instance();
	$CI->load->database();


    $config_app['hostname'] = $CI->db->hostname;
    $config_app['username'] = $CI->db->username;
    $config_app['password'] = $CI->db->password;
    $config_app['database'] = $name_db;
    $config_app['dbdriver'] = 'mysqli';
    $config_app['dbprefix'] = '';
    $config_app['pconnect'] = FALSE;
    $config_app['db_debug'] = TRUE;
    return $config_app;
}


///=============Added By Amey=========////
function print_r_custom($expresion, $hidden=false)
{
	if ($hidden) echo '<pre style="display:none;">';
        else echo '<pre>';
	print_r($expresion);
	echo '</pre>';
}
///=============Added By Amey=========////


function fbc_cartesian_product($arrays)
{
    $result = array();
    $arrays = array_values($arrays);
    $sizeIn = sizeof($arrays);
    $size = $sizeIn > 0 ? 1 : 0;
    foreach ($arrays as $array)
        $size = $size * sizeof($array);
    for ($i = 0; $i < $size; $i ++)
    {
        $result[$i] = array();
        for ($j = 0; $j < $sizeIn; $j ++)
            array_push($result[$i], current($arrays[$j]));
        for ($j = ($sizeIn -1); $j >= 0; $j --)
        {
            if (next($arrays[$j]))
                break;
            elseif (isset ($arrays[$j]))
                reset($arrays[$j]);
        }
    }
    return $result;
}

function get_next_letter($i){
	return chr((((ord($i) - 65) + 1) % 26) + 65);
}

function is_split_order($order_number){

    $split_order_number = explode("-",$order_number);
    if(count($split_order_number) === 1){
         $array = array('split_flag'=>0,'order_id'=>$order_number);
         return $array;
     }else{
         $lastPart = end($split_order_number);
        if(strlen($lastPart) === 1){
            $removed = array_pop($split_order_number);
            $main_order = join("-",$split_order_number);
            $array = array('split_flag'=>1,'order_id'=>$main_order,'last_splited'=>$lastPart);

            return $array;
        }else{
            $array = array('split_flag'=>0,'order_id'=>$order_number);
            return $array;
        }
    }
}

function sis_convert_currency($fromCurrency,$toCurrency,$base_exchange_rate) {
	static $exchangeRates;

	if($fromCurrency==$toCurrency){
		return $base_exchange_rate;
	}
	$fromCurrency = urlencode($fromCurrency);
	$toCurrency = urlencode($toCurrency);
	if(!isset($exchangeRates)){
		$exchangeRates = json_decode(file_get_contents('https://api.currencyapi.com/v2/latest?apikey=b9f21a20-90d8-11ec-abfd-0fbd954b5a15&base_currency='.$fromCurrency), true)['data'] ?? [];
	}

	$exhangeRate = $exchangeRates[$toCurrency] ?? sis_convert_currency_google($fromCurrency, $toCurrency, 1);

	return round($base_exchange_rate * $exhangeRate, 2);
}

function sis_convert_currency_google($fromCurrency, $toCurrency) {
	$url  = "https://www.google.com/search?q=".$fromCurrency."+to+".$toCurrency;
	$get = file_get_contents_curl($url);
	$get = explode('<div class="BNeawe iBp4i AP7Wnd">',$get);
	$get = explode("</div>",$get[2]);

	$price_per_one= preg_replace("/[^0-9,.]/", null, $get[0]);
	if( strpos($price_per_one, ',') !== false ) {
		$price_per_one=str_replace(',','.',$price_per_one);
	}

	return $price_per_one;
}


function file_get_contents_curl($url) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);


    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

function getBarcodeUrl($content){
    $barcode_url = 'https://sis-barcode-generator.netlify.app/?content='.$content.'&includetext';
    return $barcode_url;
}

function getWebSiteLogo($shop_id,$shop_logo){
	return get_s3_url($shop_logo, $shop_id);
}

function get_s3_bucket($shop_id = null): string
{
    $CI =& get_instance();
    $CI->load->model('WebshopModel');
    $shop_id = $shop_id ?? $_SESSION['ShopID'];
    return $CI->WebshopModel->getSingleS3Bucket($shop_id);
}

function get_s3_base_url($shop_id = null): string
{
	if(!$shop_id){
		$CI =& get_instance();
		$shop_id = $CI->session->userdata('ShopID');
	}
	if($shop_id === 'admin'){
		return "https://fbc-admin.s3.amazonaws.com/";
	}
    $bucket = get_s3_bucket($shop_id);
	return "https://{$bucket}.s3.amazonaws.com/";
}

function get_s3_url($filename, $shop_id = null): string
{
	return get_s3_base_url($shop_id).$filename;
}

function getWebsiteUrl($shop_id,$url){
    $CI =& get_instance();
    $CI->load->database();

    $q = $CI->db->query('SELECT org_website_address FROM fbc_users_shop WHERE shop_id = ?',array($shop_id));
    $data = $q->result_array();
    if($data[0]['org_website_address'] !=""){
        return $data[0]['org_website_address'];
    }else{

        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            $burl =$regs['domain'];
            $webshop_address = "https://shop".$shop_id.".".$burl;
            return $webshop_address;
          //return $regs['domain'];
        }
    }
}

function is_logged_in(){
    return !not_logged_in();
}

function not_logged_in(){
    return empty($_SESSION['LoginID']);
}

function pro_actual_price($price,$tax_percent){
     return $price/((100+$tax_percent)/100);
}
function pro_price_tax_new_actual($pro_actual_price,$tax_percent){
     return ($pro_actual_price*$tax_percent)/100;
}

function pro_price_incl_tax($pro_price_new,$discount_percent){
 return $pro_price_new - ($pro_price_new*$discount_percent)/100;
}

function pro_price_excl_tax($pro_price_incl_tax,$new_tax_percent){
 return $pro_price_incl_tax/((100+$new_tax_percent)/100);
}
function new_discount_amount($pro_price_new,$discount_percent){
 return ($pro_price_new*$discount_percent)/100;
}
function shipping_tax_amount($shipping_charge,$tax_percent){
 return ($shipping_charge*$tax_percent)/100;
}
