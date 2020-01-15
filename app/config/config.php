<?php

date_default_timezone_set("Africa/Lagos");
set_time_limit(0);
define('LIVE', false);
ini_set('display_errors', LIVE ? 'off' : 'on');
error_reporting(LIVE ? 0 : E_ALL);
define('APP_SCHEME', ($_SERVER['HTTPS'] ?? 'off') == 'on' ? "https" : 'http');
define('APP_ROOT_PATH', 'Your app path');
define('APP_ROOT_FOLDER', 'Your app root folder name');
define('APP_FOLDER', 'Your app folder name');
define('APP_PATH', APP_ROOT_PATH . "/" . APP_FOLDER);
define('QUE_PATH', APP_ROOT_PATH . '/system/que');
define('APP_HOST', APP_SCHEME . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
define('APP_HOST_FOLDER', APP_SCHEME . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/" . APP_FOLDER);
define('APP_TITLE', 'Your app title');
define('APP_NAME', 'Your app name');
define('APP_DESC', "Your app description.");
define('APP_FAV_ICON', 'template/asset/image/favicon.png');
define('APP_ICON', 'template/assets/image/icon.png');
define('APP_LOGO_WHITE', 'template/asset/image/logo-white.png');
define('APP_LOGO_BLACK', 'template/asset/image/logo-black.png');
define('APP_LOGO_LARGE', '/template/asset/image/logo-large.png');
define('APP_LOGO', '/template/asset/image/logo-large.png');
define('APP_EMAIL_BG', '/template/asset/image/mail-cover.png');
define('APP_ROBOTS', 'index, follow');
define('APP_KEYWORDS', 'SEO keywords');
define('APP_DOMAIN', APP_HOST);

/**
 * Session Settings
 */
define('SESSION_TIMEOUT', true);
define('SESSION_TIMEOUT_TIME', 3600); //1 hour
define('SESSION_REGENERATION', true);
define('SESSION_REGENERATION_TIME', 1800); //30 minutes

/**
 * This page will receive a 'Title' and a 'Message'
 * Capture data as follows
 * Title: $data.title
 * Message: $data.message
 */
define('APP_ERROR_TMP', LIVE ? (APP_PATH . '/template/module/error/error.html') : null);

define('CSRF', true);

define('APP_EMAIL_REPLY', "wisdomemenike70@gmail.com");
define('APP_EMAIL_DEFAULT', "wisdomemenike70@gmail.com");
define("APP_SMTP_HOST", "smtp.sendgrid.net");
define("APP_SMTP_PORT", "465"); // 25 normal , 465 gmail ssl, 587 gmail tsl
define("APP_SMTP_USER", "openheavens");
define("APP_SMTP_PASS", 'XNywD2nbWHBFBNox');
define("APP_SMTP_TRANSPORT", 'ssl'); // ssl , tls or ''
define("APP_SMTP_DEBUG", 0); // 0: no output , 1: commands, 2: data and commands, 3: as 2 plus connection status,4: low level data output
define("APP_SMTP_OPTIONS", [
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ]
]);
define("APP_SMTP_AUTH", true);
define("APP_SMTP_REMOTE", true);

/**
 *
 * Storage Configurations
 */
define('CONFIG', [
    'session' => [
        'memcached' => [
            'host' => '127.0.0.1',
            'port' => 11211
        ],
        'redis' => [
            'host' => '127.0.0.1',
            'port' => 6379
        ]
    ],
    'database' => [
        'mysql' => [
            'name' => 'funded',
            'user' => 'root',
            'pass' => '',
            'host' => 'localhost'
        ]
    ]
]);

define('APP_HOME_PAGE', '/');

define('APP_TIME', time());
define('APP_YEAR', date('Y'));

define("JWT_KEY", sha1("MAY GOD HELP US ALL -- [86#08*43@+17&60!57]"));
define("APP_SALT", sha1("MAY GOD HAVE MERCY ON US ALL -- {86#08*43@+17&60!57}"));

//autoload defines
define('AUTOLOAD_REQUIRE', [
    APP_PATH . '/vendor/autoload.php',
    QUE_PATH . '/vendor/autoload.php',
    QUE_PATH . '/constants.php',
    QUE_PATH . '/functions.php',
    QUE_PATH . '/algorithms.php'
]);

define('AUTOLOAD_EXCLUDE', [
    'app/cache',
    'app/storage',
    'app/template',
    'que/assets',
    'lab',
    'vendor'
]);


define('APP_TEMP_HEADER', [
    'app_title' => APP_TITLE,
    'app_name' => APP_NAME,
    'app_desc' => APP_DESC,
    'app_fav_icon' => APP_FAV_ICON,
    'app_icon' => APP_ICON,
    'app_logo_white' => APP_LOGO_WHITE,
    'app_logo_black' => APP_LOGO_BLACK,
    'app_logo_large' => APP_LOGO_LARGE,
    'app_keywords' => APP_KEYWORDS,
    'app_robots' => APP_ROBOTS,
    'app_url' => APP_DOMAIN,
    'year' => APP_YEAR
]);

define('APP_TEMP_CSS', [
    'css/style.css?e=' . APP_TIME
]);

define('APP_TEMP_SCRIPT', [
    'js/theme.js?e=' . APP_TIME
]);