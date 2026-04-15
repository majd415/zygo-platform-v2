<?php
require_once 'config.php';

try {
    $db = getDB();
    $email = 'admin@zygo.com';
    $password = 'admin123';
    $name = 'System Admin';
    $role = 'admin';
    
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([$name, $email, $hashedPassword, $role]);
    
    echo "\n[SUCCESS] Admin user created successfully!\n";
    echo "Email: $email\n";
    echo "Password: $password\n\n";
    
} catch (Exception $e) {
    echo "\n[ERROR] Could not create admin: " . $e->getMessage() . "\n\n";
}
?>
