<?php
require_once 'config.php';
$db = getDB();

try {
    echo "Adding language column to users...\n";
    $db->exec("ALTER TABLE `users` ADD `language` VARCHAR(5) DEFAULT 'en' AFTER `status` ");
    echo "Language column added successfully!";
} catch (Exception $e) {
    echo "Notice: " . $e->getMessage();
}
?>
