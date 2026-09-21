<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = ['admin', 'discipline1', 'teacher1', 'student1', 'parent1'];
foreach ($users as $u) {
    $user = \App\Models\User::where('Username', $u)->first();
    if (!$user) {
        echo "User {$u}: NOT FOUND!\n";
    } else {
        $pw1 = \Illuminate\Support\Facades\Hash::check('password123', $user->Password);
        $pw2 = \Illuminate\Support\Facades\Hash::check('password', $user->Password);
        $pw3 = \Illuminate\Support\Facades\Hash::check('123456', $user->Password);
        echo "User {$u} (Role={$user->Role}): pw123=" . ($pw1 ? 'YES' : 'NO') . ", pw=" . ($pw2 ? 'YES' : 'NO') . ", 123456=" . ($pw3 ? 'YES' : 'NO') . "\n";
    }
}
