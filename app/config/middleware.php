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
    | Middleware register
    |--------------------------------------------------------------------------
    |
    | Here are a list of middleware you could use for route registrations
    |
    */

    'user.auth' => \app\middleware\UserMiddleware::class
];