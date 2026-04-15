<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

header('Content-Type: application/json');

try {
    DB::statement("ALTER TABLE settings ADD COLUMN search_radius_km DECIMAL(8,2) DEFAULT 5.00 AFTER price_per_km_syp");
    echo json_encode(['status' => 'success', 'message' => 'Column added manually']);
} catch (\Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
