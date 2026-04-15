<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

header('Content-Type: application/json');

$userId = 11;
$user = User::find($userId);

echo json_encode(['user' => $user], JSON_PRETTY_PRINT);
