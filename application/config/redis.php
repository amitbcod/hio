<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Redis settings
| -------------------------------------------------------------------------
| Your Memcached servers can be specified below.
|
|	See: https://codeigniter.com/userguide3/libraries/caching.html#redis-caching
|
*/

$config['socket_type'] = 'tcp'; //`tcp` or `unix`
//$config['socket'] = '/var/run/redis.sock'; // in case of `unix` socket type
$config['host'] = defined('REDIS_HOST') ? REDIS_HOST : '127.0.0.1';
$config['password'] = NULL;
$config['port'] = 6379;
$config['timeout'] = 0;
