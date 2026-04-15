<?php
require_once 'config.php';
require_once 'models/Model.php';
$model = new Model();
$res = $model->query("DESCRIBE rides")->fetchAll();
echo json_encode($res, JSON_PRETTY_PRINT);
?>
