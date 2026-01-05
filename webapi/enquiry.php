<?php
header("Content-Type: application/json");

// Read raw POST body
// This will be empty if you use a normal form submit
$rawData = file_get_contents("php://input");
var_dump($rawData);


// Decode JSON into PHP array
$input = json_decode($rawData, true);

// Check if decoding worked
if (!$input) {
    echo json_encode([
        "status" => "error",
        "message" => "No JSON received or invalid JSON"
    ]);
    exit;
}

// Print the received data
echo "<pre>";
print_r($input);
echo "</pre>";
?>
 