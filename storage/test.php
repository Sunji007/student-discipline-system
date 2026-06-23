<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('Username', 'student01')->first();
if ($user) {
    $role = strtolower($user->Role);
    echo "role standard strtolower: " . $role . "\n";
    echo "Is it equal to 'นักเรียน'? " . ($role === 'นักเรียน' ? 'YES' : 'NO') . "\n";
    echo "Is original equal to 'นักเรียน'? " . ($user->Role === 'นักเรียน' ? 'YES' : 'NO') . "\n";
}
