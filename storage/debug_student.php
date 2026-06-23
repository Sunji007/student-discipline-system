<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = App\Models\User::where('Role','นักเรียน')->get();
foreach ($users as $u) {
    $s = $u->student;
    echo "User: {$u->UserID}  username:{$u->username}  role:{$u->Role}\n";
    echo "  -> Student: " . ($s ? "StudentID={$s->StudentID} Name={$s->FullName}" : "NULL (no student linked)") . "\n";
}
