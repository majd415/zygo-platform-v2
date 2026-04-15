<?php
file_put_contents('settings_debug_start.log', "SCRIPT START AT " . date('Y-m-d H:i:s') . "\nPOST: " . print_r($_POST, true), FILE_APPEND);
require_once '../config.php';
require_once '../models/Model.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents('settings_debug_start.log', "REACHED POST BLOCK\n", FILE_APPEND);
    try {
        $model = new Model();
        
        $data = [
            'price_per_km_syp' => $_POST['price_per_km_syp'] ?? 0,
            'search_radius_km' => $_POST['search_radius_km'] ?? 0,
            'magic_login_enabled' => (isset($_POST['magic_login_enabled']) && $_POST['magic_login_enabled'] == 1) ? 1 : 0,
            'commission_rate' => $_POST['commission_rate'] ?? 15.00,
            'waiting_fee_per_5_min_syp' => $_POST['waiting_fee_per_5_min_syp'] ?? 0,
            'min_fare_syp' => $_POST['min_fare_syp'] ?? 0,
            'comfort_multiplier' => $_POST['comfort_multiplier'] ?? 1.10,
            'premium_multiplier' => $_POST['premium_multiplier'] ?? 1.25,
        ];
        
        $id = $_POST['id'] ?? 1;
        
        $stmt = $model->update('settings', $data, $id);
        file_put_contents('settings_debug_early.log', "Update SQL executed for ID: $id\n", FILE_APPEND);
        $_SESSION['success'] = "Settings updated successfully!";
    } catch (Exception $e) {
        file_put_contents('settings_debug_early.log', "EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
        $_SESSION['error'] = "Failed to update settings: " . $e->getMessage();
    }
    
    header('Location: ../index.php?p=settings');
    exit;
}
