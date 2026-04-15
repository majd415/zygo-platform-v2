<?php
require_once 'config.php';
require_once 'models/Model.php';
$model = new Model();
$ride = $model->query("SELECT * FROM rides LIMIT 1")->fetch();
echo json_encode($ride, JSON_PRETTY_PRINT);
?>
