<?php
/**
 * Laravel Server Diagnostic Script
 */

header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<html><head><title>Laravel Deployment Diagnostics</title>";
echo "<style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; padding: 20px; background: #f8fafc; color: #1e293b; }
    h1 { color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
    h2 { color: #334155; margin-top: 30px; }
    .status { font-weight: bold; padding: 2px 8px; border-radius: 4px; display: inline-block; }
    .ok { background: #dcfce7; color: #15803d; }
    .fail { background: #fee2e2; color: #b91c1c; }
    .warning { background: #fef9c3; color: #a16207; }
    pre { background: #0f172a; color: #f8fafc; padding: 15px; border-radius: 6px; overflow-x: auto; font-family: Consolas, monospace; font-size: 14px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { text-align: left; padding: 8px; border-bottom: 1px solid #e2e8f0; }
    th { background: #f1f5f9; }
</style></head><body>";

echo "<h1>Laravel Server Deployment Diagnostics</h1>";

// 1. PHP Version
echo "<h2>1. PHP Version</h2>";
$php_version = PHP_VERSION;
$php_ok = version_compare($php_version, '8.2.0', '>=');
echo "Current PHP Version: <strong>{$php_version}</strong><br>";
if ($php_ok) {
    echo "<span class='status ok'>OK</span> PHP version is compatible with Laravel 12 (requires >= 8.2.0)";
} else {
    echo "<span class='status fail'>FAIL</span> PHP version is incompatible! Laravel 12 requires PHP 8.2.0 or higher. Please upgrade your PHP version in your hosting control panel.";
}

// 2. Required PHP Extensions
echo "<h2>2. PHP Extensions Check</h2>";
$required_extensions = [
    'openssl', 'pdo', 'mbstring', 'tokenizer', 'xml', 'ctype', 
    'json', 'bcmath', 'curl', 'fileinfo', 'pdo_mysql'
];
echo "<table><tr><th>Extension</th><th>Status</th></tr>";
foreach ($required_extensions as $ext) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? "<span class='status ok'>Loaded</span>" : "<span class='status fail'>Missing</span>";
    echo "<tr><td>{$ext}</td><td>{$status}</td></tr>";
}
echo "</table>";

// 3. File & Path Checks
echo "<h2>3. Files & Directories Check</h2>";
$paths_to_check = [
    'Private Directory' => __DIR__.'/../private',
    'Laravel Root' => __DIR__.'/../private/student-discipline-system',
    'Autoload File' => __DIR__.'/../private/student-discipline-system/vendor/autoload.php',
    'Bootstrap App' => __DIR__.'/../private/student-discipline-system/bootstrap/app.php',
    'Storage Directory' => __DIR__.'/../private/student-discipline-system/storage',
    'Storage Logs' => __DIR__.'/../private/student-discipline-system/storage/logs',
    'Storage Framework Views' => __DIR__.'/../private/student-discipline-system/storage/framework/views',
    'Storage Framework Cache' => __DIR__.'/../private/student-discipline-system/storage/framework/cache',
    'Storage Framework Sessions' => __DIR__.'/../private/student-discipline-system/storage/framework/sessions',
    'Bootstrap Cache' => __DIR__.'/../private/student-discipline-system/bootstrap/cache',
];

echo "<table><tr><th>Path</th><th>Exists</th><th>Writable</th></tr>";
foreach ($paths_to_check as $name => $path) {
    $exists = file_exists($path);
    $writable = is_writable($path);
    
    $exists_str = $exists ? "<span class='status ok'>Yes</span>" : "<span class='status fail'>No</span>";
    $writable_str = $writable ? "<span class='status ok'>Yes</span>" : ($exists ? "<span class='status fail'>No</span>" : "-");
    
    echo "<tr><td>{$name}<br><small style='color:#64748b'>{$path}</small></td><td>{$exists_str}</td><td>{$writable_str}</td></tr>";
}
echo "</table>";

// 4. Try Loading Composer & Laravel
echo "<h2>4. Loading Autoload and Bootstrap</h2>";
try {
    $autoload_path = __DIR__.'/../private/student-discipline-system/vendor/autoload.php';
    if (file_exists($autoload_path)) {
        require $autoload_path;
        echo "<span class='status ok'>OK</span> Composer Autoloader loaded successfully.<br>";
    } else {
        echo "<span class='status fail'>FAIL</span> Autoload file not found. Laravel cannot load.<br>";
    }
    
    $bootstrap_path = __DIR__.'/../private/student-discipline-system/bootstrap/app.php';
    if (file_exists($bootstrap_path)) {
        $app = require_once $bootstrap_path;
        echo "<span class='status ok'>OK</span> Laravel Application Bootstrapped successfully.<br>";
    } else {
        echo "<span class='status fail'>FAIL</span> Bootstrap file not found. Laravel cannot boot.<br>";
    }
} catch (\Throwable $e) {
    echo "<span class='status fail'>BOOT ERROR</span> An error occurred while booting Laravel:<br>";
    echo "<pre>" . $e->getMessage() . "\n\nStack Trace:\n" . $e->getTraceAsString() . "</pre>";
}

// 5. Env & Database Connection Check
echo "<h2>5. Environment & Database Check</h2>";
$env_path = __DIR__.'/../private/student-discipline-system/.env';
if (file_exists($env_path)) {
    echo "<span class='status ok'>Found</span> .env file exists.<br>";
    $env_content = file_get_contents($env_path);
    
    // Parse .env settings
    $db_settings = [];
    foreach (explode("\n", $env_content) as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $val = trim($val, "\"' ");
            if (in_array($key, ['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'DB_CONNECTION'])) {
                $db_settings[$key] = $val;
            }
        }
    }
    
    echo "Database Settings from .env:<br>";
    echo "<ul>";
    echo "<li>Connection: " . htmlspecialchars($db_settings['DB_CONNECTION'] ?? 'mysql') . "</li>";
    echo "<li>Host: " . htmlspecialchars($db_settings['DB_HOST'] ?? '') . "</li>";
    echo "<li>Port: " . htmlspecialchars($db_settings['DB_PORT'] ?? '') . "</li>";
    echo "<li>Database: " . htmlspecialchars($db_settings['DB_DATABASE'] ?? '') . "</li>";
    echo "<li>Username: " . htmlspecialchars($db_settings['DB_USERNAME'] ?? '') . "</li>";
    echo "</ul>";
    
    if (isset($db_settings['DB_HOST'])) {
        try {
            $dsn = "mysql:host={$db_settings['DB_HOST']};port=" . ($db_settings['DB_PORT'] ?? '3306') . ";dbname=" . ($db_settings['DB_DATABASE'] ?? '');
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT            => 5,
            ];
            $pdo = new PDO($dsn, $db_settings['DB_USERNAME'] ?? '', $db_settings['DB_PASSWORD'] ?? '', $options);
            echo "<span class='status ok'>SUCCESS</span> Connected to Database successfully!<br>";
        } catch (\PDOException $e) {
            echo "<span class='status fail'>DATABASE CONNECTION FAILED</span><br>";
            echo "<pre>Connection Error: " . htmlspecialchars($e->getMessage()) . "</pre>";
        }
    }
} else {
    echo "<span class='status fail'>FAIL</span> .env file not found at " . htmlspecialchars($env_path) . "<br>";
}

// 6. Recent Laravel Logs
echo "<h2>6. Recent Laravel Logs (Last 15 lines)</h2>";
$log_path = __DIR__.'/../private/student-discipline-system/storage/logs/laravel.log';
if (file_exists($log_path)) {
    $logs = file($log_path);
    $last_lines = array_slice($logs, -15);
    echo "<pre>";
    foreach ($last_lines as $line) {
        echo htmlspecialchars($line);
    }
    echo "</pre>";
} else {
    echo "No log file found at " . htmlspecialchars($log_path) . " or file is empty.";
}

echo "</body></html>";
