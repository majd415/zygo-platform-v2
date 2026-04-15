<?php
// C:\xampp\htdocs\dashboardtaxi\api\coupon_action.php
require_once '../config.php';
require_once '../models/Model.php';
require_once '../models/CouponModel.php';

if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$model = new CouponModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    
    if ($action === 'create') {
        $data = [
            'code' => $_POST['code'],
            'type' => $_POST['type'],
            'value' => (float)$_POST['value'],
            'description' => $_POST['description'],
            'starts_at' => $_POST['starts_at'],
            'expires_at' => $_POST['expires_at'],
            'status' => 'active'
        ];
        $model->createCoupon($data);
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $model->deleteCoupon($id);
    }
}

header('Location: ../index.php?p=coupons');
exit;
?>
