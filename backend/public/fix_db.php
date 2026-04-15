<?php
/**
 * c:\xampp\htdocs\taxiApp_backend\backend\public\fix_db.php
 * Standalone Database Fix Script (No Laravel Dependencies)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration from .env
$host = '127.0.0.1';
$port = '3307';
$db   = 'zego_taxi_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

echo "<h1>Standalone Database Patch</h1>";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "<p style='color:green'>Connected to database successfully.</p>";

    // Columns to add
    $columns = [
        'title_en' => 'VARCHAR(255) NULL',
        'title_ar' => 'VARCHAR(255) NULL',
        'message_en' => 'TEXT NULL',
        'message_ar' => 'TEXT NULL',
        'image' => 'VARCHAR(255) NULL',
        'link' => 'VARCHAR(255) NULL',
        'target' => "VARCHAR(255) NULL DEFAULT 'all'"
    ];

    // Ensure user_id is nullable (Fix for Integrity constraint violation)
    $pdo->exec("ALTER TABLE notifications MODIFY user_id INT NULL");
    echo "Modified <b>user_id</b> to be nullable.<br>";

    foreach ($columns as $column => $definition) {
        // Check if column exists
        $check = $pdo->query("SHOW COLUMNS FROM notifications LIKE '$column'");
        if ($check->rowCount() == 0) {
            $pdo->exec("ALTER TABLE notifications ADD $column $definition");
            echo "Added column: <b>$column</b><br>";
        } else {
            echo "Column <b>$column</b> already exists.<br>";
        }
    }

    echo "<h2 style='color:green'>Done! Notifications table is up to date.</h2>";

} catch (\PDOException $e) {
    echo "<h2 style='color:red'>Database Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Please ensure your MySQL server is running on port $port and the database '$db' exists.</p>";
}

echo "<hr><a href='/'>Return to Home</a>";
