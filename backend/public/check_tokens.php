<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

header('Content-Type: application/json');

$tokens = DB::table('personal_access_tokens')->where('tokenable_id', 6)->get();

echo json_encode(['tokens' => $tokens], JSON_PRETTY_PRINT);
