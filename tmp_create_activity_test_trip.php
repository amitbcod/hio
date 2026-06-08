<?php

$host = '127.0.0.1';
$db = 'holidaysio';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get current date and calculate 4 days ago
$today = date('Y-m-d');
$fourDaysAgo = date('Y-m-d', strtotime('-4 days'));

echo "Today: $today\n";
echo "4 days ago: $fourDaysAgo\n\n";

// Check what trips currently match the criteria
echo "=== Current trips matching criteria ===\n";
$result = $conn->query("
    SELECT id, title, start_date, end_date, feedback_request_sent_at 
    FROM trips 
    WHERE (
        DATE(end_date) = '$fourDaysAgo'
        OR (end_date IS NULL AND DATE(start_date) = '$fourDaysAgo')
    )
    AND feedback_request_sent_at IS NULL
");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']}, Title: {$row['title']}, Start: {$row['start_date']}, End: {$row['end_date']}, Sent: {$row['feedback_request_sent_at']}\n";
    }
} else {
    echo "No trips found matching criteria.\n";
}

echo "\n=== Creating test Activity Trip ===\n";

// Get a traveler_account_id
$result = $conn->query("SELECT id FROM traveler_accounts WHERE email IS NOT NULL LIMIT 1");
if ($result && $result->num_rows > 0) {
    $traveler = $result->fetch_assoc();
    $traveler_id = $traveler['id'];
    
    echo "Using traveler_account_id: $traveler_id\n";
    
    // Insert test Activity Trip with start_date = 4 days ago, end_date = NULL
    $stmt = $conn->prepare(
        "INSERT INTO trips (traveler_account_id, title, start_date, end_date, status, created_at, updated_at) 
         VALUES (?, ?, ?, NULL, ?, NOW(), NOW())"
    );
    
    $title = "Activity Trip";
    $status = "planned";
    $stmt->bind_param("isss", $traveler_id, $title, $fourDaysAgo, $status);
    $stmt->execute();
    
    echo "Created Activity Trip with ID: {$conn->insert_id}\n";
    echo "start_date: $fourDaysAgo\n";
    echo "end_date: NULL\n";
    echo "status: planned\n";
    echo "\nNow run: php artisan feedback:send-requests\n";
} else {
    echo "No traveler accounts found with email.\n";
}

$conn->close();
