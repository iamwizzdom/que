<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/21/2020
 * Time: 1:07 PM
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Mail Configuration
    |--------------------------------------------------------------------------
    |
    | Here are each of the mail configuration setup for your application.
    |
    */
    'address' => [
        'reply' => env('MAIL_FROM_ADDRESS'),
        'default' => env('MAIL_REPLY_ADDRESS')
    ],
    'smtp' => [
        'host' => env('MAIL_HOST'),
        'port' => env('MAIL_PORT'),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
        'transport' => env('MAIL_ENCRYPTION'),
        'timeout' => 120,
        'debug' => !LIVE,
        'options' => [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ],
        'auth' => true,
        'remote' => true
    ]
];