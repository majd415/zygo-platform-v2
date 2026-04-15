<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

header('Content-Type: application/json');

try {
    $users = User::latest()->take(10)->get(['id', 'name', 'phone', 'email', 'role', 'status', 'created_at']);
    echo json_encode([
        'total_count' => User::count(),
        'users' => $users
    ], JSON_PRETTY_PRINT);
} catch (\Exception $e) {
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
}
