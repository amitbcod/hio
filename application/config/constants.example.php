<?php
defined('BASEPATH') or exit('No direct script access allowed');

defined('SHOPCODE') || define('SHOPCODE', 'shop1');
defined('SHOP_ID') || define('SHOP_ID', 1);
defined('BASE_URL') ||  define('BASE_URL', '');
defined('API_URL') ||  define('API_URL', '');
defined('IMAGE_URL') || define('IMAGE_URL', '/');

defined('APP_ENCRYPTION_KEY') || define('APP_ENCRYPTION_KEY', '');

defined('SIS_SHOP_SERVER_PATH') ||  define('SIS_SHOP_SERVER_PATH', '/home/fbcspace/public_html/shopinshop.co/'.SHOPCODE);

defined('GC_SITE_KEY') ||  define('GC_SITE_KEY', '');
defined('GC_SECRETE_KEY') ||  define('GC_SECRETE_KEY', '');

defined('GC_SITE_KEY_V3') ||  define('GC_SITE_KEY_V3', '');
defined('GC_SECRETE_KEY_V3') ||  define('GC_SECRETE_KEY_V3', '');

defined('PINCODE_API_URL') ||  define('PINCODE_API_URL', '');
defined('PINCODE_API_TOKEN') ||  define('PINCODE_API_TOKEN', '');


defined('CI_SESSION_PATH') || define('CI_SESSION_PATH', 'session');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') or define('SHOW_DEBUG_BACKTRACE', true);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  or define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') or define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   or define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  or define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           or define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     or define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       or define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  or define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   or define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              or define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            or define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       or define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        or define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          or define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         or define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   or define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  or define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') or define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     or define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       or define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      or define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      or define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code


defined('SKIN_URL') ||  define('SKIN_URL', BASE_URL.'public/');
defined('SKIN_CSS') ||  define('SKIN_CSS', BASE_URL.'public/css/');
defined('SKIN_FONTS') ||  define('SKIN_FONTS', BASE_URL.'public/fonts/');
defined('SKIN_JS') ||  define('SKIN_JS', BASE_URL.'public/js/');

defined('TEMP_SKIN_IMG') ||  define('TEMP_SKIN_IMG', BASE_URL.'public/images');
defined('SITE_LOGO') ||  define('SITE_LOGO', IMAGE_URL.'/uploads');

defined('BANNER_IMG') ||  define('BANNER_IMG', IMAGE_URL.'uploads/banner/');
defined('BANNER_IMG_SIZE1') ||  define('BANNER_IMG_SIZE1', '1920x1080/');
defined('BANNER_IMG_SIZE2') ||  define('BANNER_IMG_SIZE2', '1920x370/');

defined('PRODUCT_THUMB_IMG') ||  define('PRODUCT_THUMB_IMG', IMAGE_URL.'uploads/products/thumb/');
defined('PRODUCT_ORG_IMG') ||  define('PRODUCT_ORG_IMG', IMAGE_URL.'uploads/products/original/');
defined('PRODUCT_MEDIUM_IMG') ||  define('PRODUCT_MEDIUM_IMG', IMAGE_URL.'uploads/products/medium/');
defined('PRODUCT_LARGE_IMG') ||  define('PRODUCT_LARGE_IMG', IMAGE_URL.'uploads/products/large/');

defined('PRODUCT_THUMB_PATH') ||  define('PRODUCT_THUMB_PATH', SIS_SHOP_SERVER_PATH.'/uploads/products/thumb/');
defined('PRODUCT_MEDIUM_PATH') ||  define('PRODUCT_MEDIUM_PATH', SIS_SHOP_SERVER_PATH.'/uploads/products/medium/');
defined('PRODUCT_LARGE_PATH') ||  define('PRODUCT_LARGE_PATH', SIS_SHOP_SERVER_PATH.'/uploads/products/large/');

defined('CATBUILD_PATH') ||  define('CATBUILD_PATH', SIS_SHOP_SERVER_PATH.'/uploads/catbuild_csv/');

defined('CSSJS_VERSION') ||  define('CSSJS_VERSION', '29122101'); // ddmmyy01, ddmmyy02 etc

defined('PRODUCT_DEFAULT_IMG') ||  define('PRODUCT_DEFAULT_IMG', TEMP_SKIN_IMG.'/default_product_image.png');

define('SIZECHART_MTB', array(7,301));
define('SIZECHART_WTB', array(1,298,299));
define('SIZECHART_MF', array(294,297));
define('SIZECHART_WF', array(294,297));
