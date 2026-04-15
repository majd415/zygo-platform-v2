<?php
require_once 'config.php';
require_once 'models/Model.php';
require_once 'models/DriverModel.php';

$driverModel = new DriverModel();

$id = (int)($_GET['id'] ?? 0);
if ($id == 0) {
    // Pick the latest driver
    $id = $driverModel->query("SELECT id FROM users WHERE role = 'driver' ORDER BY id DESC LIMIT 1")->fetch()['id'] ?? 0;
}

echo "--- Diagnostic for Driver ID: $id ---\n";

$user = $driverModel->query("SELECT * FROM users WHERE id = ?", [$id])->fetch();
$docs = $driverModel->query("SELECT * FROM driver_documents WHERE user_id = ?", [$id])->fetch();

echo "USER TABLE:\n";
if ($user) {
    print_r($user);
} else {
    echo "User not found.\n";
}

echo "\nDOCUMENTS TABLE:\n";
if ($docs) {
    print_r($docs);
} else {
    echo "Documents record not found.\n";
}

echo "\n--- Attempting Update ---\n";
// Simulate the updateDocStatus call
$status = 'approved';
$reason = 'verified manually';
$res = $driverModel->updateDocumentStatus($docs['id'] ?? 0, $status, $reason, $id);

echo "Update result: " . ($res ? "Query Sent" : "Query Failed") . "\n";

$userAfter = $driverModel->query("SELECT id, name, status, rejection_reason FROM users WHERE id = ?", [$id])->fetch();
echo "USER AFTER UPDATE:\n";
print_r($userAfter);
?>
