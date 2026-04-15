<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use App\Models\User;
$u = User::find(11);
if ($u) {
    $u->role = 'rider';
    $u->save();
    echo "User 11 (majd) is now a rider.\n";
} else {
    echo "User 11 not found.\n";
}
