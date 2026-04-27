<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=holidaysio;charset=utf8mb4','root','', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$stmt = $pdo->query("SHOW COLUMNS FROM accommodations LIKE 'og_description'");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode($row) . PHP_EOL;
