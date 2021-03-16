<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 6/5/2020
 * Time: 12:23 PM
 */

use que\middleware\AddTokensToCookie;
use que\middleware\AddTokensToHeaderResponse;
use que\middleware\CheckAuthentication;
use que\middleware\CheckForAllowedRequestIP;
use que\middleware\CheckForAllowedRequestMethod;
use que\middleware\CheckForAllowedRequestPort;
use que\middleware\CheckForMaintenanceMode;
use que\middleware\StartSession;
use que\middleware\VerifyCsrfToken;

return [


    /*
    |--------------------------------------------------------------------------
    | Global HTTP Middleware
    |--------------------------------------------------------------------------
    |
    | Here are a list of middleware that run during every request to your application
    |
    */
    'global' => [
        StartSession::class,
        CheckAuthentication::class,
        CheckForAllowedRequestMethod::class,
        CheckForAllowedRequestIP::class,
        CheckForAllowedRequestPort::class,
        CheckForMaintenanceMode::class,
        VerifyCsrfToken::class,
        AddTokensToHeaderResponse::class,
        AddTokensToCookie::class
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    |
    | Here are a list of middleware you can assign to an individual route or a group
    |
    */
    'route' => [
        'user.auth' => \app\middleware\UserMiddleware::class
    ]
];
