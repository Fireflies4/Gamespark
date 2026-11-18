<?php

//Haal de configuratie op
require_once __DIR__ . '/config.php';

//Met behulp van PDO zetten we de connectie op, waarna we met setAttribute de manier van errormeldingen weergeven bepalen.
$pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
