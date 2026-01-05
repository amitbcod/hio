<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once __DIR__ . '/../libraries/LinkUrl.php';

if (!function_exists('dd')) {
    function dd()
    {
        echo "<pre style='background-color: #b9bbbe; padding: 10px;'>";
        var_dump(func_get_args());
        echo "</pre>";
        exit;
    }
}

function linkUrl($url)
{
    return LinkUrl::from($url);
}

function verifyRecaptcha(): bool
{
    if (empty($_POST['g-recaptcha-response'])) {
        return false;
    }

    $recaptcha = new \ReCaptcha\ReCaptcha(GC_SECRETE_KEY_V3);
    $hostname = parse_url(BASE_URL, PHP_URL_HOST);
    $resp = $recaptcha->setExpectedHostname($hostname)
        ->verify($_POST['g-recaptcha-response']);
    if ($resp->isSuccess()) {
        return true;
    }

    $errors = $resp->getErrorCodes();
    return false;
}

function generateToken($length, $type = '')
{
    if ($type == 'Numeric') {
        $characters = '0123456789';
    } elseif ($type == 'Alphabetic') {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    } else {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }

    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}



function file_get_contents_curl($url)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

function convert_currency_website($price, $currency_conversion_rate, $currency_symbol)
{
    $convertedAmount =  $currency_conversion_rate * $price;
    $convertedAmount =  $currency_symbol . number_format($convertedAmount, 2);
    return $convertedAmount;
}

function getBarcodeUrl($content)
{
    $barcode_url = 'https://sis-barcode-generator.netlify.app/?content=' . $content . '&includetext';
    return $barcode_url;
}

function ip_visitor_country()
{
    static $cCode;

    if (isset($cCode)) {
        return $cCode;
    }

    if (isset($_SERVER["HTTP_CF_IPCOUNTRY"])) {
        $cCode = $_SERVER["HTTP_CF_IPCOUNTRY"];
        return $cCode;
    }

    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];
    $country  = "Unknown";
    if (filter_var($client, FILTER_VALIDATE_IP)) {
        $ip = $client;
    } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
        $ip = $forward;
    } else {
        $ip = $remote;
    }
    // $ch = curl_init();
    // curl_setopt($ch, CURLOPT_URL, "http://www.geoplugin.net/json.gp?ip=".$ip);
    // curl_setopt($ch, CURLOPT_HEADER, 0);
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // $ip_data_in = curl_exec($ch); // string
    // curl_close($ch);

    // $ip_data = json_decode($ip_data_in, true);
    // $ip_data = str_replace('&quot;', '"', $ip_data);
    // $cCode = '';
    // if ($ip_data && array_key_exists('geoplugin_countryName', $ip_data)) {
    //     if($ip_data['geoplugin_countryName'] != null){
    //         $country = $ip_data['geoplugin_countryName'];
    //         $cCode = $ip_data['geoplugin_countryCode'];
    //     }
    // }
    // return $cCode;
}

function get_preferred_browser_language()
{
    return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2);
}

function is_firefox(): bool
{
    return (bool) strpos($_SERVER['HTTP_USER_AGENT'] ?? '', 'Firefox');
}

function imgix_url(string $path, array $params = []): string
{
    if (!defined('IMGIX_URL')) {
        return IMAGE_URL . '/' . $path;
    }

    $params = array_merge(['auto' => 'format'], $params);

    return IMGIX_URL . '/' . $path . '?' . http_build_query($params);
}

function distributor_country($country_code)
{
    $result = array();
    switch ($country_code) {

        case 'HU':
            $country_name = lang('Hungary');
            $click_here_link = "https://www.zumba-shop.hu/";
            break;

        case 'GR':
            $country_name = lang('Greece');
            $click_here_link = "https://zumbashop.gr/";
            break;

        case 'PL':
            $country_name = lang('Poland');
            $click_here_link = "https://sklep.zumbasklep.pl/";
            break;

        case 'TR':
            $country_name = lang('Turkey');
            $click_here_link = "https://www.zumbawearturkiye.com/";
            break;

        case 'RU':
            $country_name = lang('Russia');
            $click_here_link = "https://zumbastore.ru/";
            break;

        case 'ZA':
            $country_name = lang('South_Africa');
            $click_here_link = "https://zumbawearshopsa.myshopify.com/";
            break;

        default:
            $country_name = '';
            $click_here_link = '';
    }
    array_push($result, $country_name, $click_here_link);
    return $result;
}

function change_url($g_url)
{
    if ($g_url == 'tell-me-why-magazine') {
        echo  $url = 'tell-me-why-magazine-0';
        die();
        return $url;
    } else {
        return false;
    }
}
