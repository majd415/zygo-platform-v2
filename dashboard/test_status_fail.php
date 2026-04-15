<?php
require_once 'config.php';
require_once 'models/Model.php';
require_once 'models/DriverModel.php';

$driverModel = new DriverModel();

echo "--- Status Update Test ---\n";

// 1. Find a driver with no documents or a specific driver ID
$driver = $driverModel->query("SELECT u.id FROM users u LEFT JOIN driver_documents dd ON u.id = dd.user_id WHERE u.role = 'driver' AND dd.id IS NULL LIMIT 1")->fetch();

if (!$driver) {
    // Create one for testing if none exist
    $driverModel->query("INSERT INTO users (name, phone, role, status) VALUES ('Test Driver', '+123456789', 'driver', 'pending')");
    $userId = $driverModel->query("SELECT LAST_INSERT_ID() as id")->fetch()['id'];
    echo "Created mock driver with ID: $userId\n";
} else {
    $userId = $driver['id'];
    echo "Using existing driver with ID: $userId (No documents)\n";
}

// 2. Simulate the 'update_doc_status' action as seen in api/driver_action.php
$docId = 0; // No document
$status = 'approved';
$userIdToUpdate = $userId;

echo "Attempting to approve driver $userIdToUpdate with docId $docId...\n";

// This is what api/driver_action.php does currently:
if ($docId > 0) {
    $driverModel->updateDocumentStatus($docId, $status, null, $userIdToUpdate);
} else {
    echo "SKIPPED: docId is 0, so implementation in api/driver_action.php currently does nothing!\n";
}

// 3. Check if user status changed
$statusInDb = $driverModel->query("SELECT status FROM users WHERE id = ?", [$userIdToUpdate])->fetch()['status'];
echo "Final User Status in DB: $statusInDb\n";

if ($statusInDb !== 'active') {
    echo "FAILURE: User status was not updated to 'active' because docId was 0.\n";
} else {
    echo "SUCCESS: User status updated.\n";
}
?>
