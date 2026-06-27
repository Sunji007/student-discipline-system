<?php
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Find Composer Autoloader
$autoload = null;
if (@file_exists($path = __DIR__.'/../vendor/autoload.php')) {
    $autoload = $path;
} elseif (@file_exists($path = __DIR__.'/../private/vendor/autoload.php')) {
    $autoload = $path;
} elseif (@file_exists($path = __DIR__.'/../private/student-discipline-system/vendor/autoload.php')) {
    $autoload = $path;
} elseif (@file_exists($path = __DIR__.'/student-discipline-system/vendor/autoload.php')) {
    $autoload = $path;
}

if ($autoload) {
    require $autoload;
} else {
    die("Composer autoload not found.");
}

// Find Bootstrap App
$bootstrap = null;
if (@file_exists($path = __DIR__.'/../bootstrap/app.php')) {
    $bootstrap = $path;
} elseif (@file_exists($path = __DIR__.'/../private/bootstrap/app.php')) {
    $bootstrap = $path;
} elseif (@file_exists($path = __DIR__.'/../private/student-discipline-system/bootstrap/app.php')) {
    $bootstrap = $path;
} elseif (@file_exists($path = __DIR__.'/student-discipline-system/bootstrap/app.php')) {
    $bootstrap = $path;
}

if ($bootstrap) {
    $app = require_once $bootstrap;
} else {
    die("Bootstrap not found.");
}

// Query User and RolePermission
try {
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    echo "<h3>1. User check for teacher01</h3>";
    $user = \App\Models\User::where('Username', 'teacher01')->first();
    if ($user) {
        echo "UserID: " . $user->UserID . "<br>";
        echo "Username: " . $user->Username . "<br>";
        echo "FullName: " . $user->FullName . "<br>";
        echo "Role: [" . $user->Role . "]<br>";
        echo "Status: " . $user->Status . "<br>";
        echo "canAccess('messages'): " . ($user->canAccess('messages') ? 'TRUE' : 'FALSE') . "<br>";
        echo "canAccess('behavior-records'): " . ($user->canAccess('behavior-records') ? 'TRUE' : 'FALSE') . "<br>";
    } else {
        echo "User teacher01 not found!<br>";
    }

    echo "<h3>2. RolePermission records for Role = 'ครู'</h3>";
    $perms = \App\Models\RolePermission::where('Role', 'ครู')->get();
    foreach ($perms as $perm) {
        echo "Module: " . $perm->ModuleName . " | CanAccess: " . $perm->CanAccess . "<br>";
    }
    
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage();
}
