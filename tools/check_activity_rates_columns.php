<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=holidaysio','root','');
$stmt = $pdo->query("SHOW COLUMNS FROM activity_rates LIKE 'valid_from'");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($cols);
$stmt = $pdo->query("SHOW COLUMNS FROM activity_rates LIKE 'valid_to'");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($cols);
