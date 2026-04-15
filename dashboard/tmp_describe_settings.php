<?php
require_once 'config.php';
require_once 'models/Model.php';
$model = new Model();
try {
    $res = $model->query("DESCRIBE settings")->fetchAll();
    echo json_encode($res, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
