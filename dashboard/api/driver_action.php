<?php
require_once '../config.php';
require_once '../models/Model.php';
require_once '../models/DriverModel.php';

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    die('Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = (int)($_POST['user_id'] ?? 0);
    $driverModel = new DriverModel();
    
    $redirectTo = $_POST['redirect_to'] ?? '../index.php?p=drivers';
    
    if ($action === 'update_status') {
        $status = $_POST['status'] ?? 'inactive';
        $reason = $_POST['rejection_reason'] ?? null;
        $driverModel->updateDriverStatus($userId, $status, $reason);
        header('Location: ' . $redirectTo . '&status=updated');
    } elseif ($action === 'verify') {
        $isVerified = (int)($_POST['is_verified'] ?? 1);
        $driverModel->verifyDriver($userId, $isVerified);
        
        $docId = (int)($_POST['doc_id'] ?? 0);
        if ($docId > 0) {
            $docStatus = $isVerified ? 'approved' : 'rejected';
            $reason = $_POST['rejection_reason'] ?? null;
            $driverModel->updateDocumentStatus($docId, $docStatus, $reason, $userId);
        }
        
        header('Location: ' . $redirectTo . '&status=verified');
    } elseif ($action === 'update_doc_status') {
        $docId = (int)($_POST['doc_id'] ?? 0);
        $status = $_POST['status'] ?? 'pending';
        $reason = $_POST['rejection_reason'] ?? null;
        
        // Always try to update user status if userId is valid, 
        // updateDocumentStatus handles the user activation logic too.
        $driverModel->updateDocumentStatus($docId, $status, $reason, $userId);
        
        header('Location: ' . $redirectTo . '&status=updated');
    } elseif ($action === 'send_notification') {
        require_once '../models/NotificationModel.php';
        $notifModel = new NotificationModel();
        $notifModel->broadcast([
            'title_en' => $_POST['title_en'] ?? $_POST['title_ar'] ?? 'Notification',
            'title_ar' => $_POST['title_ar'] ?? 'تنبيه',
            'message_en' => $_POST['message_en'] ?? $_POST['message_ar'] ?? '',
            'message_ar' => $_POST['message_ar'] ?? '',
            'type' => 'important',
            'target' => 'specific',
            'user_id' => $userId
        ]);
        header('Location: ' . $redirectTo . '&status=notified');
    } elseif ($action === 'delete') {
        $driverModel->deleteDriver($userId);
        header('Location: ../index.php?p=drivers&status=deleted');
    } else {
        header('Location: ' . $redirectTo . '&status=unknown_action');
    }
    exit;
}
?>
