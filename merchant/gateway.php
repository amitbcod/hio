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

//Transaction details and credentials of merchant
//Keys
$totalAmount = "1.00";
$appID = "1000003657";  // input PROD APPID here
//$merTradeNo = microtime(true)*1000; // generate unique merTradeNo
$merTradeNo = (int)(microtime(true) * 1000); 
$paymentType = "S";
$publicKey = "MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAkNj7mHVuArZ8E4ObKW5Nkd12ZRqktEZGt8VtoclbM8LaBWK91C91PGp9E5XiEsQHl1gDmRZiynIYNxs1VCqat7uTIy/ifiQzKLGE8g9BGez7zunmp5sVSzfoXa3/1cESl+PEWqc6Oe097QBvBnmWyvqIvnA8yrZ6Ie6cTefqqZ8bM3SBP2/nfS5SbYuoKG7tbNlEOwM0Wxd3RaMQUAH9CnWcpqoh/9iFuJNExCThRg7CSPniuz+LYJyO8ee6CsUMppQpEfbg9KmM5gCbydVlwVRk0BmkUGaSn//rCuog9+SjWBw2KFjcRGwl6F6JqbhgUNcFmpP4z6tXayNyklHuKRvfmRLnUZaJWINIpVCNVf8rXkNp5HJU8fhdv1jSs8/iK18Xa7rB3yYAXD04OlnicXRY3YE0uR6j1/TEfkZGDEDIRuhaM7K1kOkuVUIGhhlNVOEykT2J/r6k+k9tw+mGELnITpuHTdFpz0KprbjAmWZkAmY9jGvvx/nMvp9j89kdC4AH9p+8fJouwsnnW/CG2Sa4rO+P5CBoqoDLekAi8yVoSrh7b2j9CEYhX7y7OPSOrzfeggjB+PS5XCEHGHPos9y3BrSbap2EuU43yziCUMihihGoQ9fl5xiWe1u9eNUXbA7dcsr1prI2evc8/fQN+VGg1F+cKkCE7qZdFUzkEsUCAwEAAQ=="; // input Public Key here
$apiKey = "704CV2x1aZevGwrA+e4yB+9OAldsdWmXrxsKwPnuMgfvTgJXbHVpl68bCsD57jIHceagNAQRMiQyvF1KvOoZlA=="; // input API Key here

$payload=array(
        "totalPrice"=>$totalAmount,
        "currency"=>"MUR",
        "merTradeNo"=>$merTradeNo,
        "notifyUrl" => "https://ymstore.whuso.in/merchant/notifyurl.php",
        "returnUrl" => "https://ymstore.whuso.in/merchant/notifyurl.php",
        "remark"=>"This is a test payment",
        "lang"=>"en"
    );

//Encoding payload to JSON 
$payload=json_encode($payload);

//Constructing the signature using RSA
$rsa = new \phpseclib\Crypt\RSA();
$rsa->setHash( 'sha1' );
$rsa->setMGFHash( 'sha1' );
$rsa->setEncryptionMode( RSA::ENCRYPTION_OAEP  );
$rsa->setPublicKeyFormat(RSA::PUBLIC_FORMAT_PKCS1);
$rsa->loadKey($publicKey); // public key
$encryptedPayload = base64_encode($rsa->encrypt($payload));
$signaturedata = "appId=$appID&merTradeNo=$merTradeNo&payload=$encryptedPayload&paymentType=$paymentType";
$sign = base64_encode(hash_hmac('sha512', $signaturedata, $apiKey,true));
    
//Displaying transaction details
echo "APPID: " . $appID  . "<br>";
echo "MerchantTradeNo: " . $merTradeNo  . "<br>";
echo "PaymentType: " . $paymentType  . "<br>";
echo "Encrypted Payload: " . $encryptedPayload  . "<br>";
echo "Signature: " . $sign  . "<br>";

?>

<!--- Sending request my.t money gateway using form and POST method --->

<html>
<body>

<form action="https://pay.mytmoney.mu/Mt/web/payments" method="post">
<input type="hidden" name="appId" value="<?php echo $appID; ?>"/>
<input type="hidden" name="merTradeNo" value="<?php echo $merTradeNo; ?>" />
<input type="hidden" name="payload" value="<?php echo $encryptedPayload; ?>"/>
<input type="hidden" name="paymentType" value="<?php echo $paymentType; ?>"/>
<input type="hidden" name="sign" value="<?php echo $sign; ?>"/>
<p><input type="submit" value="Pay By my.t money"/></p>
</form>

</body>
</html>
