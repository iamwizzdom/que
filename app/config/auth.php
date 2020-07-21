<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/21/2020
 * Time: 12:22 PM
 */

use que\security\JWT\JWT;

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Configuration
    |--------------------------------------------------------------------------
    |
    | Here are each of the authentication configuration setup for your application.
    |
    */

    'csrf' => LIVE,
    'jwt' => [
        'key' => '',
        'algo' => JWT::ALGORITHM_HS512
    ],
    'app' => [
        'salt' => '',
        'secret' => '',
        'version' => ''
    ]
];