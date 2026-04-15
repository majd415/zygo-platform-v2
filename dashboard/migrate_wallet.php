<?php
// C:\xampp\htdocs\dashboardtaxi\migrate_wallet.php
require_once 'config.php';
$db = getDB();

$queries = [
    "CREATE TABLE IF NOT EXISTS recharge_cards (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(20) UNIQUE NOT NULL,
        balance DECIMAL(10, 2) NOT NULL,
        expiry_date DATE,
        usage_limit INT DEFAULT 1,
        status ENUM('active', 'used', 'expired') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS recharge_card_usage (
        id INT AUTO_INCREMENT PRIMARY KEY,
        card_id INT NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (card_id) REFERENCES recharge_cards(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )"
];

foreach ($queries as $q) {
    try {
        $db->exec($q);
        echo "Success: Table/Migration applied.\n";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
