<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

header('Content-Type: application/json');

$users = User::select('id', 'name', 'role', 'fcm_token', 'status')->get();

echo json_encode(['users' => $users], JSON_PRETTY_PRINT);
