<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = App\Models\User::all();
echo "Total users: " . $users->count() . "\n";
foreach ($users as $u) {
    echo "ID: " . $u->UserID . " | Username: " . $u->Username . " | Role: " . $u->Role . "\n";
}
