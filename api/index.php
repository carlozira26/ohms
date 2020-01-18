<?php
define("ENV", "development");
set_time_limit(0);
require __DIR__ . '/vendor/autoload.php';

date_default_timezone_set('Asia/Manila');

$settings = require __DIR__ . '/settings/settings.php';
$app = new \Slim\App($settings);

// Setup functions
require( __DIR__ . '/settings/functions.php');

// DB connection
require( __DIR__ . '/settings/dbconnect.php');

// CORS Settings
require( __DIR__ . '/settings/cors.php');

// Controller register
require( __DIR__ . '/settings/controllers.php');

// routes register
require( __DIR__ . '/settings/routes.php');

$app->run();
?>
