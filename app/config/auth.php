<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/21/2020
 * Time: 12:22 PM
 */

use que\security\jwt\JWT;

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
        | JWT time to live
        |--------------------------------------------------------------------------
        |
        | Specify the length of time (in minutes) that the token will be valid for.
        | Defaults to 1 hour.
        |
        | You can also set this to null, to yield a never expiring token.
        | This behaviour might come in handy for e.g. a mobile app.
        | This is not particularly recommended, so make sure you have appropriate
        | systems in place to revoke the token if necessary.
        | Notice: If you set this to null you should remove 'exp' element from 'required_claims' list.
        |
        */
        'ttl' => TIMEOUT_ONE_HOUR,

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