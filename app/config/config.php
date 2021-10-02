<?php

date_default_timezone_set("Africa/Lagos");
set_time_limit(0);
const LIVE = false; // Set to [bool](true) in production mode otherwise [bool](false) in development mode
ini_set('display_errors', LIVE ? 'off' : 'on');
error_reporting(LIVE ? 0 : E_ALL);

define('APP_SCHEME', ($_SERVER['HTTPS'] ?? 'off') == 'on' ? "https" : 'http');
define('APP_ROOT_PATH', dirname(__DIR__, 2)); // Your app root path
define('APP_ROOT_FOLDER', basename(APP_ROOT_PATH)); // Your app root folder name
const APP_PACKAGE_NAME = APP_ROOT_FOLDER;
const APP_PATH = (APP_ROOT_PATH . '/app');
const QUE_PATH = (APP_ROOT_PATH . '/system/que');
define('APP_HOST', APP_SCHEME . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));

//autoload defines
const AUTOLOAD_PATH = [
    APP_PATH, QUE_PATH
];

const AUTOLOAD_CACHE_PATH = (QUE_PATH . '/cache');

const AUTOLOAD_REQUIRE = [
    APP_PATH . '/vendor/autoload.php',
    QUE_PATH . '/vendor/autoload.php',
    QUE_PATH . '/constants.php',
    QUE_PATH . '/functions.php',
    QUE_PATH . '/algorithms.php'
];

const AUTOLOAD_EXCLUDE = [
    'que/cache',
    'app/storage',
    'app/template',
    'que/assets',
    'app/vendor',
    'que/vendor',
    'lab'
];
