<?php
// C:\xampp\htdocs\dashboardtaxi\api\generate_cards.php
require_once '../config.php';
require_once '../models/Model.php';
require_once '../models/WalletModel.php';

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    die('Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $count = (int)$_POST['count'];
    $balance = (float)$_POST['balance'];
    $expiry = $_POST['expiry'] ?: null;
    $batchId = 'BATCH-' . date('Ymd-His') . '-' . mt_rand(100, 999);

    $model = new WalletModel();
    $model->generateCards($count, $balance, $expiry, $batchId);

    header('Location: ../index.php?p=wallet&status=generated&batch=' . $batchId);
    exit;
}
?>
