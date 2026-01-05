<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// General Website Settings


// SMTP Settings
define('SMTP_HOST', '');
define('SMTP_PORT', '');
define('SMTP_UNAME', '');
define('SMTP_PWORD', '');

// Database Settings
defined('DB_HOST') ||  define('DB_HOST','');
defined('DB_USER') ||  define('DB_USER','');
defined('DB_PASS') ||  define('DB_PASS','');
defined('DB_PORT') ||  define('DB_PORT','');
defined('DB_DBASE') ||  define('DB_DBASE','');
defined('DB_PREFIX') ||  define('DB_PREFIX','fbcspace_');

//database name prefix
defined('DB_NAME_PREFIX') ||  define('DB_NAME_PREFIX','fbcspace_shopinshop_shop');

// Google Captcha Settings
defined('GC_SITE_KEY') ||  define('GC_SITE_KEY','');
defined('GC_SECRETE_KEY') ||  define('GC_SECRETE_KEY','');

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
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

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
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

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
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code


defined('MAX_FILESIZE_BYTE') ||  define('MAX_FILESIZE_BYTE', "2097152"); // In 2MB (Bytes) 2 * 1024 * 1024
defined('MAX_FILESIZE_MB') ||  define('MAX_FILESIZE_MB', "2 MB"); // In MB

defined('BASE_URL') ||  define('BASE_URL','https://shopinshop.co/');
defined('SIS_SERVER_PATH') ||  define('SIS_SERVER_PATH','/home/fbcspace/public_html/shopinshop.co');

defined('SITE_TITLE') ||  define('SITE_TITLE','Shop In Shop');


defined('SKIN_URL') ||  define('SKIN_URL',BASE_URL.'public/');
defined('SKIN_CSS') ||  define('SKIN_CSS',BASE_URL.'public/css/');
defined('SKIN_JS') ||  define('SKIN_JS',BASE_URL.'public/js/');
defined('SKIN_IMG') ||  define('SKIN_IMG',BASE_URL.'public/images/');


defined('PRODUCT_GALLERY_PATH') ||  define('PRODUCT_GALLERY_PATH','/uploads/products/');

defined('IMPORTS_PATH') ||  define('IMPORTS_PATH','/uploads/imports/');


defined('DB_PREFIX') ||  define('DB_PREFIX','fbcspace_');
defined('DATE_PIC_FM') ||  define('DATE_PIC_FM','d-m-Y');
defined('SIS_DATE_FM') ||  define('SIS_DATE_FM','d/m/Y');
defined('SIS_DATE_FM_WT') ||  define('SIS_DATE_FM_WT','d/m/Y | h:i A');


defined('CSSJS_VERSION') ||  define('CSSJS_VERSION','11012201'); // ddmmyy01, ddmmyy02 etc

defined('MESSAGE_URL') ||  define('MESSAGE_URL',BASE_URL.'public/uploads/messaging/');
