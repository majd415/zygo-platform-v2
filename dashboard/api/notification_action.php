<?php
// C:\xampp\htdocs\dashboardtaxi\api\notification_action.php
require_once '../config.php';
require_once '../models/Model.php';
require_once '../models/NotificationModel.php';

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    die('Forbidden');
}

$model = new NotificationModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $model->deleteNotification($_POST['id']);
        header('Location: ../index.php?p=notifications&status=deleted');
        exit;
    }
    
    if ($_POST['action'] === 'bulk_delete' && !empty($_POST['ids'])) {
        $model->deleteMultipleNotifications($_POST['ids']);
        header('Location: ../index.php?p=notifications&status=deleted');
        exit;
    }
}

header('Location: ../index.php?p=notifications');
exit;
?>
