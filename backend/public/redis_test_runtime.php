<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// Bootstrap the application to load .env and config
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Redis;

header('Content-Type: application/json');

$results = [
    'php_extension_loaded' => extension_loaded('redis'),
    'redis_class_exists' => class_exists('Redis'),
    'predis_installed' => class_exists('Predis\Client'),
    'env_config' => [
        'host' => env('REDIS_HOST'),
        'port' => env('REDIS_PORT'),
        'client' => env('REDIS_CLIENT'),
    ],
    'tests' => []
];

try {
    // Try to ping using the Facade (which respects REDIS_CLIENT)
    $ping = Redis::connection()->ping();
    $results['tests']['ping'] = [
        'success' => true,
        'response' => is_string($ping) ? $ping : (is_bool($ping) ? ($ping ? 'PONG' : 'FAIL') : json_encode($ping))
    ];

    Redis::set('diagnostic_test', 'success_at_'.time());
    $get = Redis::get('diagnostic_test');
    $results['tests']['set_get'] = [
        'success' => $get !== false,
        'value' => $get
    ];

} catch (Throwable $e) {
    $results['tests']['error'] = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ];
}

echo json_encode($results, JSON_PRETTY_PRINT);
