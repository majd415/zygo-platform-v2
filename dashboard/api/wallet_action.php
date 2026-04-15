<?php
require_once '../config.php';
require_once '../models/Model.php';
require_once '../models/WalletModel.php';

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    die('Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    
    $walletModel = new WalletModel();
    
    if ($action === 'delete') {
        $walletModel->deleteCard($id);
        header('Location: ../index.php?p=wallet&status=deleted');
    } else {
        header('Location: ../index.php?p=wallet&status=unknown_action');
    }
    exit;
}
?>
