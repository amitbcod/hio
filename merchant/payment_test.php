<?php 
//Include libraries
include('phpseclib/phpseclib/Crypt/RSA.php');
include('phpseclib/phpseclib/Math/BigInteger.php');
include('phpseclib/phpseclib/Crypt/Hash.php');
include('phpseclib/phpseclib/Crypt/Random.php');
use phpseclib\Crypt\Base;
use phpseclib\Crypt\RSA;
use phpseclib\Crypt\SSH2;
use phpseclib\Crypt\Hash;


// -------------------------
// Configuration (inline)
// -------------------------
$appID = "1000003657";  // input PROD APPID here
$apiKey = "704CV2x1aZevGwrA+e4yB+9OAldsdWmXrxsKwPnuMgfvTgJXbHVpl68bCsD57jIHceagNAQRMiQyvF1KvOoZlA==";
$publicKey = "MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAkNj7mHVuArZ8E4ObKW5Nkd12ZRqktEZGt8VtoclbM8LaBWK91C91PGp9E5XiEsQHl1gDmRZiynIYNxs1VCqat7uTIy/ifiQzKLGE8g9BGez7zunmp5sVSzfoXa3/1cESl+PEWqc6Oe097QBvBnmWyvqIvnA8yrZ6Ie6cTefqqZ8bM3SBP2/nfS5SbYuoKG7tbNlEOwM0Wxd3RaMQUAH9CnWcpqoh/9iFuJNExCThRg7CSPniuz+LYJyO8ee6CsUMppQpEfbg9KmM5gCbydVlwVRk0BmkUGaSn//rCuog9+SjWBw2KFjcRGwl6F6JqbhgUNcFmpP4z6tXayNyklHuKRvfmRLnUZaJWINIpVCNVf8rXkNp5HJU8fhdv1jSs8/iK18Xa7rB3yYAXD04OlnicXRY3YE0uR6j1/TEfkZGDEDIRuhaM7K1kOkuVUIGhhlNVOEykT2J/r6k+k9tw+mGELnITpuHTdFpz0KprbjAmWZkAmY9jGvvx/nMvp9j89kdC4AH9p+8fJouwsnnW/CG2Sa4rO+P5CBoqoDLekAi8yVoSrh7b2j9CEYhX7y7OPSOrzfeggjB+PS5XCEHGHPos9y3BrSbap2EuU43yziCUMihihGoQ9fl5xiWe1u9eNUXbA7dcsr1prI2evc8/fQN+VGg1F+cKkCE7qZdFUzkEsUCAwEAAQ=="; // input Public Key here
$notifyUrl = "https://ymstore.whuso.in/merchant/notify_test.php"; // server callback
$returnUrl = "https://ymstore.whuso.in/merchant/callback_test.php"; // browser redirect

// -------------------------
// Transaction details
// -------------------------
$totalAmount = "1.00";
$merTradeNo = (int)(microtime(true) * 1000);
$paymentType = "S";

$payload = [
    "totalPrice" => $totalAmount,
    "currency" => "MUR",
    "merTradeNo" => $merTradeNo,
    "notifyUrl" => $notifyUrl,
    "returnUrl" => $returnUrl,
    "remark" => "Test payment",
    "lang" => "en"
];

$payloadJson = json_encode($payload);



$rsa = new RSA();
$rsa->setHash('sha1');
$rsa->setMGFHash('sha1');
$rsa->setEncryptionMode(RSA::ENCRYPTION_OAEP);
$rsa->setPublicKeyFormat(RSA::PUBLIC_FORMAT_PKCS1);
$rsa->loadKey($publicKey);
$encryptedPayload = base64_encode($rsa->encrypt($payloadJson));

// -------------------------
// Generate HMAC-SHA512 signature
// -------------------------
$signatureData = "appId=$appID&merTradeNo=$merTradeNo&payload=$encryptedPayload&paymentType=$paymentType";
$sign = base64_encode(hash_hmac('sha512', $signatureData, $apiKey, true));

?>
<html>
<body>
<h2>Test My.T Money Payment</h2>
<form action="https://pay.mytmoney.mu/Mt/web/payments" method="post">
    <input type="hidden" name="appId" value="<?php echo $appID; ?>"/>
    <input type="hidden" name="merTradeNo" value="<?php echo $merTradeNo; ?>" />
    <input type="hidden" name="payload" value="<?php echo $encryptedPayload; ?>"/>
    <input type="hidden" name="paymentType" value="<?php echo $paymentType; ?>"/>
    <input type="hidden" name="sign" value="<?php echo $sign; ?>"/>
    <input type="submit" value="Pay By My.T Money"/>
</form>
</body>
</html>
