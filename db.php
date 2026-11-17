<?php
// Databaseverbinding voor Gamespark (zelfde database als in phpMyAdmin)
$host = '127.0.0.1';
$dbname = 'gamespark';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Verbinding mislukt: ' . $e->getMessage());
}
