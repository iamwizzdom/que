<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 6/3/2020
 * Time: 11:55 PM
 */

use que\error\log\transport\ConsoleTransport;
use que\error\log\transport\FileTransport;

return [
    /*
    |--------------------------------------------------------------------------
    | Log Configuration
    |--------------------------------------------------------------------------
    |
    | Here you define a path for logging Que Runtime errors
    |
    */
    'error' => [
        'path' => APP_PATH . "/error/log",
        'filename' => null
    ],
    'transport' => [
        PapertrailTransport::class,
        ConsoleTransport::class,
        FileTransport::class
    ]
];