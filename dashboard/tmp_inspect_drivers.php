<?php
require_once 'config.php';
try {
    $db = getDB();
    echo "--- DRIVERS ---\n";
    $drivers = $db->query("SELECT id, name, avatar, status FROM users WHERE role = 'driver' LIMIT 5")->fetchAll();
    print_r($drivers);
    echo "\n--- DOCUMENTS ---\n";
    $docs = $db->query("SELECT * FROM driver_documents LIMIT 5")->fetchAll();
    print_r($docs);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
