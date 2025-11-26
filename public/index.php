<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/vendor/autoload.php'; // Mode Hosting
// require __DIR__.'/../vendor/autoload.php'; // Mode Dev

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/bootstrap/app.php'; // Mode Hosting
// $app = require_once __DIR__.'/../bootstrap/app.php'; // Mode Dev

$app->handleRequest(Request::capture());
