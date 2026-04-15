<?php
require_once 'config.php';
$db = getDB();
$tables = ['car_types', 'advertisements', 'notifications'];
foreach($tables as $t) {
    echo "--- TABLE: $t ---\n";
    try {
        print_r($db->query("SHOW COLUMNS FROM `$t`")->fetchAll());
    } catch(Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
