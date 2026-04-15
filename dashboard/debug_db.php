<?php
// C:\xampp\htdocs\dashboardtaxi\debug_db.php
require_once 'config.php';
$db = getDB();
$tables = ['users', 'rides', 'driver_documents', 'wallet_transactions', 'settings'];
foreach($tables as $t) {
    echo "--- TABLE: $t ---\n";
    print_r($db->query("SHOW COLUMNS FROM `$t`")->fetchAll());
}
?>
