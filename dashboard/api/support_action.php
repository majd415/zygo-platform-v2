<?php
require_once '../config.php';
require_once '../models/Model.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $model = new Model();
        
        $data = [
            'whatsapp_phone' => $_POST['whatsapp_phone'] ?? null,
            'email_support' => $_POST['email_support'] ?? null,
            'support_phone' => $_POST['support_phone'] ?? null,
        ];
        
        $id = $_POST['id'] ?? 1;
        
        $stmt = $model->update('settings', $data, $id);
        $_SESSION['success'] = "Support details updated successfully!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to update support details: " . $e->getMessage();
    }
    
    header('Location: ../index.php?p=support');
    exit;
}
