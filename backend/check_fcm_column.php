<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$columns = Schema::getColumnListing('users');
echo "Columns in users table: \n";
print_r($columns);

$fcm_token_exists = Schema::hasColumn('users', 'fcm_token');
echo "fcm_token column exists: " . ($fcm_token_exists ? 'YES' : 'NO') . "\n";
