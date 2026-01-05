<?php
// Inline API key
$apiKey = "704CV2x1aZevGwrA+e4yB+9OAldsdWmXrxsKwPnuMgfvTgJXbHVpl68bCsD57jIHceagNAQRMiQyvF1KvOoZlA==";

// Retrieve POST data
$data = $_POST;

// Exclude 'sign' from verification
$fieldsForSign = [];
foreach ($data as $key => $value) {
    if ($key !== 'sign') {
        $fieldsForSign[$key] = $value;
    }
}

// Sort fields alphabetically
ksort($fieldsForSign);

// Build query string
$queryParts = [];
foreach ($fieldsForSign as $key => $value) {
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
    // Optional: Payment success check
    if (isset($data['tradeStatus']) && $data['tradeStatus'] === 'TRADE_FINISHED') {
        echo "✅ Payment finished!\n";
    }
} else {
    echo "❌ Signature mismatch!\n";
}
echo "</pre>";
?>
