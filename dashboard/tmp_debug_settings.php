<?php
require_once 'config.php';
require_once 'models/Model.php';
$model = new Model();
$settings = $model->all('settings');
echo json_encode($settings, JSON_PRETTY_PRINT);
?>
