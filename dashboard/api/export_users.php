<?php
// C:\xampp\htdocs\dashboardtaxi\api\export_users.php
require_once '../config.php';
require_once '../models/UserModel.php';

// Auth Check
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    die('Unauthorized');
}

$userModel = new UserModel();

$ids = isset($_GET['ids']) && $_GET['ids'] !== '' ? explode(',', $_GET['ids']) : [];
$role = $_GET['role'] ?? '';
$search = $_GET['search'] ?? '';

$users = $userModel->getUsersForExport($ids, $role, $search);

$filename = "users_export_" . date('Y-m-d_H-i') . ".csv";

// Set Headers for Download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Open the output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add CSV Headers
fputcsv($output, [
    'ID',
    'Name',
    'Email',
    'Phone',
    'Role',
    'Status',
    'Joined At'
]);

// Add Data Rows
foreach ($users as $u) {
    fputcsv($output, [
        $u['id'],
        $u['name'],
        $u['email'],
        $u['phone'],
        ucfirst($u['role']),
        ucfirst($u['status'] ?? 'Active'),
        $u['created_at']
    ]);
}

fclose($output);
exit();
?>
