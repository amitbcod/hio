<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=holidaysio','root','');
// Print last 50 activity_rates
$stmt = $pdo->query("SELECT rate_id, activity_id, variant_id, season, rate_specificity, adult_rate, equipment_rate, valid_from, valid_to, created_at, updated_at FROM activity_rates ORDER BY created_at DESC LIMIT 50");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Last 50 activity_rates:\n";
foreach ($rows as $r) {
    echo implode(' | ', [
        $r['rate_id'] ?? '',
        $r['activity_id'] ?? '',
        $r['variant_id'] ?? '',
        $r['season'] ?? '',
        $r['rate_specificity'] ?? '',
        $r['adult_rate'] ?? '',
        $r['equipment_rate'] ?? '',
        $r['valid_from'] ?? 'NULL',
        $r['valid_to'] ?? 'NULL',
        $r['created_at'] ?? '',
    ]) . "\n";
}

// Print Package-specific rows
$stmt = $pdo->query("SELECT rate_id, activity_id, variant_id, season, rate_specificity, adult_rate, equipment_rate, valid_from, valid_to, created_at FROM activity_rates WHERE season = 'Package' ORDER BY created_at DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\nPackage rows:\n";
if (count($rows) === 0) {
    echo "(none)\n";
} else {
    foreach ($rows as $r) {
        echo implode(' | ', [
            $r['rate_id'] ?? '',
            $r['activity_id'] ?? '',
            $r['variant_id'] ?? '',
            $r['season'] ?? '',
            $r['rate_specificity'] ?? '',
            $r['adult_rate'] ?? '',
            $r['equipment_rate'] ?? '',
            $r['valid_from'] ?? 'NULL',
            $r['valid_to'] ?? 'NULL',
            $r['created_at'] ?? '',
        ]) . "\n";
    }
}
