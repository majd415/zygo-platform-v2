<?php
// C:\xampp\htdocs\dashboardtaxi\api\driver_approval.php
require_once '../config.php';
require_once '../models/Model.php';
require_once '../models/DriverModel.php';

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    die('Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $action = $_POST['action'];
    $userId = $_POST['user_id'] ?? null;
    $reason = $_POST['reason'] ?? null;

    $model = new DriverModel();
    
    if ($action === 'approve') {
        $model->updateDocumentStatus($id, 'approved');
        // If it's a critical document, verify the user
        // For simplicity, we'll verify if any document is approved
        if ($userId) $model->verifyDriver($userId);
    } else {
        $model->updateDocumentStatus($id, 'rejected', $reason);
    }

    header('Location: ../index.php?p=drivers');
    exit;
}
?>
