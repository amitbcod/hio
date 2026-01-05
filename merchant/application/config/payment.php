<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['myt_money'] = [
    'app_id'       => '1000003657',        // My.T Money APP ID
    'api_key'      => '704CV2x1aZevGwrA+e4yB+9OAldsdWmXrxsKwPnuMgfvTgJXbHVpl68bCsD57jIHceagNAQRMiQyvF1KvOoZlA==',       // My.T Money API Key
    'public_key'   => "MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAkNj7mHVuArZ8E4ObKW5Nkd12ZRqktEZGt8VtoclbM8LaBWK91C91PGp9E5XiEsQHl1gDmRZiynIYNxs1VCqat7uTIy/ifiQzKLGE8g9BGez7zunmp5sVSzfoXa3/1cESl+PEWqc6Oe097QBvBnmWyvqIvnA8yrZ6Ie6cTefqqZ8bM3SBP2/nfS5SbYuoKG7tbNlEOwM0Wxd3RaMQUAH9CnWcpqoh/9iFuJNExCThRg7CSPniuz+LYJyO8ee6CsUMppQpEfbg9KmM5gCbydVlwVRk0BmkUGaSn//rCuog9+SjWBw2KFjcRGwl6F6JqbhgUNcFmpP4z6tXayNyklHuKRvfmRLnUZaJWINIpVCNVf8rXkNp5HJU8fhdv1jSs8/iK18Xa7rB3yYAXD04OlnicXRY3YE0uR6j1/TEfkZGDEDIRuhaM7K1kOkuVUIGhhlNVOEykT2J/r6k+k9tw+mGELnITpuHTdFpz0KprbjAmWZkAmY9jGvvx/nMvp9j89kdC4AH9p+8fJouwsnnW/CG2Sa4rO+P5CBoqoDLekAi8yVoSrh7b2j9CEYhX7y7OPSOrzfeggjB+PS5XCEHGHPos9y3BrSbap2EuU43yziCUMihihGoQ9fl5xiWe1u9eNUXbA7dcsr1prI2evc8/fQN+VGg1F+cKkCE7qZdFUzkEsUCAwEAAQ==", // Your Public Key
    'notify_url' => 'https://ymstore.whuso.in/merchant/paymentGateway/mytNotify', // backend POST callback
	'return_url' => 'https://ymstore.whuso.in/merchant/paymentGateway/mytCallback', // browser redirect
    'mode'         => 'live'             // sandbox or live
];
