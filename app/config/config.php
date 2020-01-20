<?php

date_default_timezone_set("Africa/Lagos");
set_time_limit(0);
define('LIVE', false); // Set to [bool](true) in production mode otherwise [bool](false) in development mode
ini_set('display_errors', LIVE ? 'off' : 'on');
error_reporting(LIVE ? 0 : E_ALL);
define('APP_SCHEME', ($_SERVER['HTTPS'] ?? 'off') == 'on' ? "https" : 'http');
define('APP_ROOT_PATH', ''); // Your app root path
define('APP_ROOT_FOLDER', ''); // Your app root folder name
define('APP_FOLDER', ''); // Your app folder name
define('APP_PATH', APP_ROOT_PATH . "/" . APP_FOLDER);
define('QUE_PATH', APP_ROOT_PATH . '/system/que');
define('APP_HOST', APP_SCHEME . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
define('APP_HOST_FOLDER', APP_SCHEME . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
define('APP_TITLE', ''); // Your app title
define('APP_NAME', ''); // Your app name
define('APP_DESC', ""); // Your app description
define('APP_FAV_ICON', '');
define('APP_ICON', '');
define('APP_LOGO_WHITE', '');
define('APP_LOGO_BLACK', '');
define('APP_LOGO_LARGE', '');
define('APP_LOGO', '');
define('APP_EMAIL_BG', '');
define('APP_ROBOTS', 'index, follow');
define('APP_KEYWORDS', ''); // SEO keywords separated by comma ','
define('APP_DOMAIN', APP_HOST);

/**
 * Session Settings
 */
define('SESSION_TIMEOUT', true);
define('SESSION_TIMEOUT_TIME', 3600); //1 hour
define('SESSION_REGENERATION', true);
define('SESSION_REGENERATION_TIME', 1800); //30 minutes

/**
 * This page will receive a 'Title', a 'Message' and a 'Code'
 * Capture data as follows
 * Title: $data.title
 * Message: $data.message
 * Code: $data.code -- [HTTP response code]
 */
define('APP_ERROR_TMP', LIVE ? (APP_PATH . '/template/module/error/error.html') : null);

define('CSRF', true);

define('APP_EMAIL_REPLY', ""); // Your default reply to email address
define('APP_EMAIL_DEFAULT', ""); // Your default email address
define("APP_SMTP_HOST", ""); // Your email host
define("APP_SMTP_PORT", "465"); // 25 normal , 465 gmail ssl, 587 gmail tsl
define("APP_SMTP_USER", ""); // Your email username
define("APP_SMTP_PASS", ''); // Your email account password
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
            'host' => '127.0.0.1', // Memcached host ip address
            'port' => 11211, // Memcached port
            'enable' => true // Set to [bool](true) to enable memcached, otherwise [bool](false) to disable
        ],

        'redis' => [
            'host' => '127.0.0.1', // Redis host ip address
            'port' => 6379, // Redis port
            'enable' => true // Set to [bool](true) to enable redis, otherwise [bool](false) to disable
        ]

        /**
         * @Note: For optimum performance make sure to enable either memcached or redis;
         * better still, have both enabled.
         * However, if both are disabled, Que will fall back to its native caching system (QueKip)
         */

    ],

    'database' => [

        'mysql' => [
            'name' => 'que', // database name
            'user' => 'root', // username
            'pass' => '', // password
            'host' => 'localhost', // MySQL host / ip address
            'port' => null, // MySQL port
            'socket' => null, // MySQL socket
            'debug' => true // Set to [bool](true) to shutdown Que and output all MySQL/SQL errors,
            // otherwise [bool](false) to output only FATAL errors

        ]

    ],
    'db_table' => [
        'user' => [
            'name' => '', // Your app user table name
            'primary_key' => '', // Your app user table primary key name
            'status_key' => 'is_active' // Your table column for managing record status
        ],
        'country' => [
            'name' => '', // Your app user table name
            'primary_key' => '', // Your app user table primary key name
            'status_key' => 'is_active' // Your table column for managing record status
        ],
        'state' => [
            'name' => '', // Your app user table name
            'primary_key' => '', // Your app user table primary key name
            'status_key' => 'is_active' // Your table column for managing record status
        ],
        'area' => [
            'name' => '', // Your app user table name
            'primary_key' => '', // Your app user table primary key name
            'status_key' => 'is_active' // Your table column for managing record status
        ],
        'language' => [
            'name' => '', // Your app user table name
            'primary_key' => '', // Your app user table primary key name
            'status_key' => 'is_active' // Your table column for managing record status
        ]
    ]
]);

define('APP_HOME_PAGE', '/'); // Your app home page url

define("JWT_KEY", sha1("")); // Your JWT key
define("APP_SALT", sha1("")); // Your app salt key

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
    'year' => '2018'
]);

define('APP_TEMP_CSS', [
    // Your default template css files
]);

define('APP_TEMP_SCRIPT', [
    // Your default template js files
]);