<?php
$host = '127.0.0.1';
$port = '3307';
$user = 'root';
$pass = '';
$db   = 'zego_taxi_db';

try {
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $db");
    echo "Successfully created database: $db\n";
} catch (PDOException $e) {
    die("Error creating database: " . $e->getMessage() . "\n");
}
