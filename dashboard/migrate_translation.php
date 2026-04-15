<?php
require_once 'config.php';
$db = getDB();

try {
    echo "Starting DB Migrations...\n";

    // 1. Advertisements Table
    echo "Migrating advertisements...\n";
    $db->exec("ALTER TABLE `advertisements` 
        CHANGE `title` `title_en` VARCHAR(255) NOT NULL,
        ADD `title_ar` VARCHAR(255) AFTER `title_en`,
        CHANGE `description` `description_en` VARCHAR(255) NULL,
        ADD `description_ar` VARCHAR(255) AFTER `description_en`,
        CHANGE `button_text` `button_text_en` VARCHAR(255) NOT NULL DEFAULT 'Explore',
        ADD `button_text_ar` VARCHAR(255) AFTER `button_text_en`
    ");
    echo "Advertisements migrated.\n";

    // 2. Notifications Table
    echo "Migrating notifications...\n";
    $db->exec("ALTER TABLE `notifications` 
        CHANGE `title` `title_en` TEXT NOT NULL,
        ADD `title_ar` TEXT AFTER `title_en`,
        CHANGE `body` `message_en` TEXT NOT NULL,
        ADD `message_ar` TEXT AFTER `message_en`
    ");
    echo "Notifications migrated.\n";

    echo "Migrations successfully completed!";
} catch (Exception $e) {
    die("Migration failed: " . $e->getMessage());
}
?>
