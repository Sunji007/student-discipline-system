<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
} elseif (file_exists($maintenance = __DIR__.'/../private/storage/framework/maintenance.php')) {
    require $maintenance;
} elseif (file_exists($maintenance = __DIR__.'/student-discipline-system/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
if (file_exists($autoload = __DIR__.'/../vendor/autoload.php')) {
    require $autoload;
} elseif (file_exists($autoload = __DIR__.'/../private/vendor/autoload.php')) {
    require $autoload;
} elseif (file_exists($autoload = __DIR__.'/student-discipline-system/vendor/autoload.php')) {
    require $autoload;
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
if (file_exists($bootstrap = __DIR__.'/../bootstrap/app.php')) {
    $app = require_once $bootstrap;
} elseif (file_exists($bootstrap = __DIR__.'/../private/bootstrap/app.php')) {
    $app = require_once $bootstrap;
} elseif (file_exists($bootstrap = __DIR__.'/student-discipline-system/bootstrap/app.php')) {
    $app = require_once $bootstrap;
}

$app->handleRequest(Request::capture());

