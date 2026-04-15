<?php
$logPath = __DIR__.'/../storage/logs/laravel.log';
if (!file_exists($logPath)) {
    echo "Log file not found.";
    exit;
}

$lines = file($logPath);
$recentLines = array_slice($lines, -100);

foreach ($recentLines as $line) {
    if (str_contains($line, 'ERROR') || str_contains($line, 'Exception')) {
        echo $line . "\n";
    }
}
