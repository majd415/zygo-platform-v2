<?php
file_put_contents('post_diag.log', "POST received at " . date('Y-m-d H:i:s') . "\n" . print_r($_POST, true), FILE_APPEND);
header('Location: ../index.php?p=settings');
exit;
?>
