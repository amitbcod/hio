<?php

$date4DaysAgo = date('Y-m-d', strtotime('-4 days'));
echo "Today: " . date('Y-m-d') . "\n";
echo "4 days ago: $date4DaysAgo\n\n";

// Connect to database directly
$host = getenv('DB_HOST') ?: 'localhost';
$db = getenv('DB_DATABASE') ?: 'holidaysio';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    
    // Check for trips matching criteria
    $stmt = $pdo->prepare("SELECT id, end_date, status, feedback_request_sent_at FROM trips WHERE DATE(end_date) = ? AND status = 'Completed'");
    $stmt->execute([$date4DaysAgo]);
    $trips = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Trips with end_date = $date4DaysAgo and status = 'Completed':\n";
    echo "Count: " . count($trips) . "\n";
    foreach ($trips as $trip) {
        echo "  Trip {$trip['id']}: end_date={$trip['end_date']}, status={$trip['status']}, sent_at={$trip['feedback_request_sent_at']}\n";
    }
    
    // Show recent trips to see the date range
    echo "\n\nRecent 20 trips (for reference):\n";
    $stmt = $pdo->query("SELECT id, end_date, status, feedback_request_sent_at FROM trips ORDER BY end_date DESC LIMIT 20");
    $recentTrips = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($recentTrips as $t) {
        echo "  Trip {$t['id']}: end_date={$t['end_date']}, status={$t['status']}, sent_at={$t['feedback_request_sent_at']}\n";
    }
    
} catch (Exception $e) {
    echo "Database connection error: " . $e->getMessage() . "\n";
}
