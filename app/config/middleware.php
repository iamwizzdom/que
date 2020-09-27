<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 6/5/2020
 * Time: 12:23 PM
 */

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
        \que\middleware\CheckAuthentication::class,
        \que\middleware\CheckForAllowedRequestMethod::class,
        \que\middleware\CheckForMaintenanceMode::class,
        \que\middleware\VerifyCsrfToken::class
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
