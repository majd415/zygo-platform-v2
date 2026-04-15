<?php
require_once '../config.php';
require_once '../models/Model.php';

$model = new Model();
try {
    // 1. Add commission_rate to settings
    $result = $model->query("SHOW COLUMNS FROM settings LIKE 'commission_rate'");
    if (!$result->fetch()) {
        $model->query("ALTER TABLE settings ADD COLUMN commission_rate DECIMAL(5,2) DEFAULT 15.00");
        echo "Migration: Added 'commission_rate' to settings.\n";
    }

    // 2. Add commission_amount to rides
    $result = $model->query("SHOW COLUMNS FROM rides LIKE 'commission_amount'");
    if (!$result->fetch()) {
        $model->query("ALTER TABLE rides ADD COLUMN commission_amount DECIMAL(15,2) DEFAULT 0.00");
        echo "Migration: Added 'commission_amount' to rides.\n";
    }

    // 3. Add driver_earnings to rides
    $result = $model->query("SHOW COLUMNS FROM rides LIKE 'driver_earnings'");
    if (!$result->fetch()) {
        $model->query("ALTER TABLE rides ADD COLUMN driver_earnings DECIMAL(15,2) DEFAULT 0.00");
        echo "Migration: Added 'driver_earnings' to rides.\n";
    }

    echo "Migration completed successfully.";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage();
}
?>
