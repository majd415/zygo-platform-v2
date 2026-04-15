<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

header('Content-Type: application/json');

$deleted = DB::table('migrations')->where('migration', 'like', '%add_search_radius%')->delete();

echo json_encode(['deleted' => $deleted], JSON_PRETTY_PRINT);
