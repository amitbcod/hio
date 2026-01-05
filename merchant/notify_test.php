<?php
// Inline API key
$apiKey = "704CV2x1aZevGwrA+e4yB+9OAldsdWmXrxsKwPnuMgfvTgJXbHVpl68bCsD57jIHceagNAQRMiQyvF1KvOoZlA==";


// Retrieve POST data
$data = $_POST;

// Fields that may be sent
$expectedFields = [
    'merTradeNo', 'msg', 'errorCode', 'tradeNo', 'tradeStatus', 'timestamp',
    'mf1','mf2','mf3','mf4','mf5'
];

// Build query string for signature verification
$queryParts = [];
foreach ($expectedFields as $key) {
    $value = isset($data[$key]) ? $data[$key] : '';
    $queryParts[] = $key . '=' . $value;
}
$queryString = implode('&', $queryParts);

// Generate signature
$generatedSign = base64_encode(hash_hmac('sha256', $queryString, $apiKey, true));

// Display debug info
echo "<pre>";
echo "Received POST:\n";
print_r($data);
echo "\nQuery String for Signature:\n$queryString\n";
echo "\nGenerated Signature: $generatedSign\n";
echo "Received Signature: " . (isset($data['sign']) ? $data['sign'] : '') . "\n";

// Verify signature
if (isset($data['sign']) && $generatedSign === $data['sign']) {
    echo "✅ Signature matched!\n";
    // Additional check for payment success
    if ($data['tradeStatus'] === 'TRADE_FINISHED' && $data['errorCode'] === '000') {
        echo "✅ Payment successful!\n";
    } else {
        echo "⚠️ Payment not successful or pending.\n";
    }
} else {
    echo "❌ Signature mismatch!\n";
}
echo "</pre>";
?>
