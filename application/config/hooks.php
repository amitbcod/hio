<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/

$hook['post_controller_constructor'][] = [
    'class' => 'CheckLiveStatus',
    'function' => 'handle',
    'filename' => 'CheckLiveStatus.php',
    'filepath' => 'hooks',
];


$hook['post_controller_constructor'][] = array(
    'class' => 'CurrencySession',
    'function' => 'setCurrencySession',
    'filename' => 'CurrencySession.php',
    'filepath' => 'hooks',
    'params' => array()
);


// $hook['pre_system'][] = array(
//     'class'    => 'Maintenance_hook',
//     'function' => 'offline_check',
//     'filename' => 'Maintenance_hook.php',
//     'filepath' => 'hooks'
// );
