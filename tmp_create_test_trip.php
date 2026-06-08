<?php

// Simple database query without Laravel bootstrap
$host = '127.0.0.1';
$db = 'holidaysio';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Today is June 8, 2026. We need trips with end_date = June 4, 2026
// First, let's see what traveler_account_ids exist
$result = $conn->query("SELECT id FROM traveler_accounts LIMIT 1");
if ($result && $result->num_rows > 0) {
    $traveler = $result->fetch_assoc();
    $traveler_id = $traveler['id'];
    
    echo "Using traveler_account_id = $traveler_id\n";
    
    // Create a test trip that matches the criteria
    $end_date = date('Y-m-d', strtotime('-4 days')); // 2026-06-04
    $start_date = date('Y-m-d', strtotime('-10 days')); // Earlier
    
    echo "Creating test trip with end_date = $end_date and status = 'completed'\n";
    
    // Check if we already have matching trips
    $check = $conn->query("SELECT COUNT(*) as cnt FROM trips WHERE DATE(end_date) = '$end_date' AND status = 'completed'");
    $row = $check->fetch_assoc();
    echo "Found {$row['cnt']} trips matching criteria\n";
    
    if ($row['cnt'] == 0) {
        // Insert a test trip
        $stmt = $conn->prepare("INSERT INTO trips (traveler_account_id, title, start_date, end_date, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("issss", $traveler_id, $title, $start_date, $end_date, $status);
        $title = "Test Trip for Feedback";
        $status = "completed";
        $stmt->execute();
        
        echo "Inserted test trip (ID: {$conn->insert_id})\n";
        echo "Run: php artisan feedback:send-requests\n";
    }
} else {
    echo "No traveler_accounts found. Please create a traveler first.\n";
}

$conn->close();
