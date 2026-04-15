<?php
$logPath = __DIR__.'/../storage/logs/laravel.log';
if (!file_exists($logPath)) {
    echo "Log file not found.";
    exit;
}

$content = file_get_contents($logPath);
$errorStart = strrpos($content, 'local.ERROR:');
if ($errorStart === false) {
    // Try production.ERROR
    $errorStart = strrpos($content, '.ERROR:');
}

if ($errorStart !== false) {
    echo substr($content, $errorStart, 1000);
} else {
    echo "No ERROR found in logs.";
}
