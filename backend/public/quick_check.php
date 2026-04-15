<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use App\Models\Setting;
$s = Setting::first();
echo "RADIUS: " . ($s->search_radius_km ?? 'DEFAULT(5)') . "\n";
echo "APP: " . ($s->app_name ?? 'NOT SET') . "\n";
