<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;

header('Content-Type: application/json');

$columns = Schema::getColumnListing('driver_documents');

echo json_encode(['columns' => $columns], JSON_PRETTY_PRINT);
