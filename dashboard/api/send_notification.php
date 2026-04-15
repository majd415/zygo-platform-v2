<?php
// C:\xampp\htdocs\dashboardtaxi\api\send_notification.php
require_once '../config.php';
require_once '../models/Model.php';
require_once '../models/NotificationModel.php';

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    die('Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model = new NotificationModel();
    $model->broadcast($_POST);

    header('Location: ../index.php?p=notifications&status=sent');
    exit;
}
?>
