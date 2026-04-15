<?php
// C:\xampp\htdocs\dashboardtaxi\check_admins.php
require_once 'config.php';
$db = getDB();
$admins = $db->query("SELECT email, name FROM users WHERE role = 'admin'")->fetchAll();
print_r($admins);
?>
