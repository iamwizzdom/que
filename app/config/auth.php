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
    | Cross Site Request Forgery
    |--------------------------------------------------------------------------
    |
    | Here you specify if you want your application to forbid CSRF with a boolean
    |
    */
    'csrf' => LIVE,

    /*
    |--------------------------------------------------------------------------
    | JSON Web Token
    |--------------------------------------------------------------------------
    |
    | Here are configurations for JWT works
    |
    */
    'jwt' => [

        /*
        |--------------------------------------------------------------------------
        | JWT Authentication Secret
        |--------------------------------------------------------------------------
        |
        | Don't forget to set this, as it will be used to sign your tokens.
        |
        | Note: This will be used for Symmetric algorithms only (HMAC).
        |
        */
        'secret' => '',

        /*
        |--------------------------------------------------------------------------
        | JWT hashing algorithm
        |--------------------------------------------------------------------------
        |
        | Specify the hashing algorithm that will be used to sign the token.
        |
        */
        'algo' => JWT::DEFAULT_ALGORITHM,

        /*
        |--------------------------------------------------------------------------
        | Required JWT Claims
        |--------------------------------------------------------------------------
        |
        | Specify the required claims that must exist in any JWT token.
        | A MissingClaimException will be thrown if any of these claims are not
        | present in the payload.
        |
        */
        'required_claims' => [
            'iss',
            'iat',
            'exp',
            'nbf',
            'sub',
            'jti',
        ],

        /*
        |--------------------------------------------------------------------------
        | Leeway
        |--------------------------------------------------------------------------
        |
        | This property gives the jwt timestamp claims some "leeway".
        | Meaning that if you have any unavoidable slight clock skew on
        | any of your servers then this will give you some level of cushioning.
        |
        | Note: This applies to the claims `iat`, `nbf` and `exp`.
        |
        | Specify in seconds - only if you know you need it.
        |
        */
        'leeway' => 0,
    ],
    'app' => [
        'salt' => '',
        'secret' => '',
        'version' => ''
    ]
];