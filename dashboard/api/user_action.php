<?php
require_once '../config.php';
session_start();

// Basic Auth check (ensure admin)
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$user_id = $_POST['user_id'] ?? null;

if (!$user_id) {
    $_SESSION['error'] = "User ID is required.";
    header("Location: ../index.php?p=users");
    exit;
}

$userModel = new UserModel();

try {
    if ($action === 'delete') {
        $result = $userModel->deleteUser($user_id);
        if ($result) {
            $_SESSION['success'] = "User deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete user.";
        }
    } elseif ($action === 'update') {
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'role' => $_POST['role'] ?? 'rider',
            'service_category' => $_POST['service_category'] ?? 'economy',
            'status' => $_POST['status'] ?? 'active',
            'password' => $_POST['password'] ?? ''
        ];
        
        $result = $userModel->updateUser($user_id, $data);
        if ($result) {
            $_SESSION['success'] = "User updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to update user.";
        }
    } elseif ($action === 'update_wallet') {
        $newBalance = (float) ($_POST['wallet_balance'] ?? 0);
        
        $db = getDB();
        $db->beginTransaction();
        
        $user = $db->query("SELECT wallet_balance, name FROM users WHERE id = $user_id")->fetch();
        if (!$user) throw new Exception("User not found.");
        
        $oldBalance = $user['wallet_balance'] ?? 0;
        $amount = $newBalance - $oldBalance;
        
        // Update user
        $db->prepare("UPDATE users SET wallet_balance = ?, updated_at = NOW() WHERE id = ?")->execute([$newBalance, $user_id]);
        
        // Log transaction
        $db->prepare("INSERT INTO wallet_transactions (user_id, transaction_type, amount, balance_before, balance_after, description, created_at, updated_at) 
                     VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())")
           ->execute([
               $user_id,
               $amount >= 0 ? 'credit' : 'debit',
               $amount,
               $oldBalance,
               $newBalance,
               "Admin manual adjustment"
           ]);
           
        $db->commit();
        $_SESSION['success'] = "Wallet for {$user['name']} updated to " . number_format($newBalance) . " SYP.";
    } else {
        $_SESSION['error'] = "Invalid action.";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

header("Location: ../index.php?p=users");
exit;
?>
