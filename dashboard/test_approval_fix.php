<?php
require_once 'config.php';
require_once 'models/Model.php';
require_once 'models/DriverModel.php';

try {
    $db = getDB();
    $driverModel = new DriverModel();
    
    // 1. Find a pending driver with docs
    $driver = $db->query("SELECT u.id, u.status, dd.id as doc_id FROM users u JOIN driver_documents dd ON u.id = dd.user_id WHERE u.role = 'driver' AND u.status = 'pending' LIMIT 1")->fetch();
    
    if (!$driver) {
        die("No pending driver found for test.");
    }
    
    echo "Testing Driver ID: " . $driver['id'] . "\n";
    echo "Initial Status: " . $driver['status'] . "\n";
    
    // 2. Approve documents
    echo "Approving documents...\n";
    $driverModel->updateDocumentStatus($driver['doc_id'], 'approved', 'Verified by system test', $driver['id']);
    
    // 3. Verify status
    $updated = $db->query("SELECT status FROM users WHERE id = " . $driver['id'])->fetch();
    echo "Updated Status: " . $updated['status'] . "\n";
    
    if ($updated['status'] === 'active') {
        echo "SUCCESS: Status synchronized.\n";
    } else {
        echo "FAILURE: Status mission mismatch.\n";
    }
    
    // 4. Test URL generation
    echo "Testing URL generation for 'driver_docs/test.jpg':\n";
    echo get_doc_url('driver_docs/test.jpg') . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
