<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/20/2020
 * Time: 9:20 PM
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Caching system Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the caching system connection setup for your application.
    | Of course, examples and explanation of configuring each caching platform that is
    | supported by Que is shown below to make development simple.
    |
    |
    | Make sure you have the driver for the below caching systems
    | installed on your machine before enabling them.
    |
    */
    'memcached' => [

        /*
         | Memcached host ip address
         */
        'host' => '127.0.0.1',

        /*
         | Memcached port
         */
        'port' => 11211,

        /*
         | Set to [bool](true) to enable memcached, otherwise [bool](false) to disable
         */
        'enable' => false
    ],

    'redis' => [

        /*
         | Redis host ip address
         */
        'host' => '127.0.0.1',

        /*
         | Redis port
         */
        'port' => 6379,

        /*
         | Set to [bool](true) to enable redis, otherwise [bool](false) to disable
         */
        'enable' => false
    ]

    /**
     * @Note: For optimum performance make sure to enable either memcached or redis;
     * better still, have them both enabled.
     * However, if both are disabled, Que will fallback to its native caching system (QueKip)
     */

];