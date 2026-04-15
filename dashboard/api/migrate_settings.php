<?php
require_once '../config.php';
require_once '../models/Model.php';

$model = new Model();
try {
    // Check if column exists
    $result = $model->query("SHOW COLUMNS FROM settings LIKE 'magic_login_enabled'");
    if (!$result->fetch()) {
        $model->query("ALTER TABLE settings ADD COLUMN magic_login_enabled TINYINT(1) DEFAULT 0");
        echo "Migration successful: Column 'magic_login_enabled' added.";
    } else {
        echo "Migration skipped: Column already exists.";
    }
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage();
}
?>
