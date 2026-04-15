<?php
require_once 'config.php';
require_once 'models/Model.php';
$model = new Model();
$data = [
    'magic_login_enabled' => 1
];
$res = $model->update('settings', $data, 1);
echo "Update result: " . ($res ? "Success" : "Failure");
?>
