<?php
require_once '../config.php';
require_once '../models/AdvertisementModel.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$model = new AdvertisementModel();

$redirectTo = '../index.php?p=advertisements';

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title_en' => $_POST['title_en'] ?? '',
        'title_ar' => $_POST['title_ar'] ?? '',
        'description_en' => $_POST['description_en'] ?? null,
        'description_ar' => $_POST['description_ar'] ?? null,
        'button_text_en' => $_POST['button_text_en'] ?? 'Explore',
        'button_text_ar' => $_POST['button_text_ar'] ?? 'استكشاف',
        'click_action' => $_POST['click_action'] ?? null,
        'sort_order' => (int)($_POST['sort_order'] ?? 0),
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'C:/xampp/htdocs/taxiApp_backend/backend/public/uploads/sliders/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileInfo = pathinfo($_FILES['image']['name']);
        // Sanitize the file
        $ext = strtolower($fileInfo['extension']);
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $ext = 'jpg';
        }
        $filename = uniqid() . '_' . time() . '.' . $ext;
        $targetFile = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $data['image_url'] = 'uploads/sliders/' . $filename;
        } else {
            $_SESSION['error'] = 'Failed to upload image inside XAMPP public directory.';
            header('Location: ' . $redirectTo);
            exit;
        }
    } else {
        $errorCode = $_FILES['image']['error'] ?? 'Unknown';
        $_SESSION['error'] = 'Image upload failed. System Error Code: ' . $errorCode;
        header('Location: ' . $redirectTo);
        exit;
    }

    if ($model->insert($data)) {
        $_SESSION['success'] = 'Advertisement active.';
    } else {
        $_SESSION['error'] = 'Database error creating ad.';
    }
} elseif ($action === 'delete') {
    $id = (int)$_POST['id'];
    if ($id && $model->deleteAdvertisement($id)) {
        $_SESSION['success'] = 'Advertisement deleted.';
    }
} elseif ($action === 'toggle') {
    $id = (int)$_POST['id'];
    if ($id && $model->toggleStatus($id)) {
        $_SESSION['success'] = 'Advertisement status updated.';
    }
}

header('Location: ' . $redirectTo);
exit;
