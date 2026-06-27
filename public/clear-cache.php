<?php
/**
 * Laravel Cache Clearing Utility
 */

header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Laravel Cache Clearing Tool</h2>";

// 1. Find Composer Autoloader
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
    echo "<p style='color:green;'>✓ Composer Autoloader loaded successfully from: $autoload</p>";
} else {
    die("<p style='color:red;'>✗ FAILED: Could not find vendor/autoload.php</p>");
}

// 2. Find Bootstrap App
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
    echo "<p style='color:green;'>✓ Laravel bootstrapped successfully from: $bootstrap</p>";
} else {
    die("<p style='color:red;'>✗ FAILED: Could not find bootstrap/app.php</p>");
}

// 3. Run Artisan Commands using Console Kernel
try {
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    
    // Clear Route Cache
    $kernel->call('route:clear');
    echo "<p>✓ Route Cache: " . nl2br(htmlspecialchars($kernel->output())) . "</p>";
    
    // Clear Config Cache
    $kernel->call('config:clear');
    echo "<p>✓ Config Cache: " . nl2br(htmlspecialchars($kernel->output())) . "</p>";
    
    // Clear General Cache
    $kernel->call('cache:clear');
    echo "<p>✓ App Cache: " . nl2br(htmlspecialchars($kernel->output())) . "</p>";
    
    // Clear View Cache
    $kernel->call('view:clear');
    echo "<p>✓ View Cache: " . nl2br(htmlspecialchars($kernel->output())) . "</p>";

    // Reset OPcache
    if (function_exists('opcache_reset')) {
        if (opcache_reset()) {
            echo "<p style='color:green;'>✓ OPcache reset successful!</p>";
        } else {
            echo "<p style='color:orange;'>⚠️ OPcache reset failed.</p>";
        }
    } else {
        echo "<p style='color:gray;'>OPcache is not enabled on this server.</p>";
    }
    
    echo "<h3 style='color:green;'>All caches cleared successfully!</h3>";
    
} catch (\Throwable $e) {
    echo "<p style='color:red;'>✗ ERROR running Artisan commands: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
