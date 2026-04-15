<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use App\Models\User;
foreach (User::all() as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Phone: {$u->phone} | Role: {$u->role}\n";
}
