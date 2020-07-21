<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/21/2020
 * Time: 9:31 AM
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    |
    | Here are each of the session configuration setup for your application.
    |
    */


    //--------------------------------------------------------------------------


    /*
    | Set to [bool](true) to enable session timeout after a specified time,
    | or [bool](false) to disable session timeout for each session to last forever,
    | until the user logs out (if a logout feature is provided)
    */
    'timeout' => true,

    /*
    | Specifies the time in seconds in which the session should expire
    */
    'timeout_time' => 3600,

    /*
    | Set to [bool](true) to enable session regeneration in which data is spooled
    | from the database to refresh session data after a specified time,
    | or [bool](false) to disable session regeneration for each session to remain the same forever.
    */
    'regeneration' => true,

    /*
    | Specifies the time in seconds in which the session data should
    | be regenerated
    */
    'regeneration_time' => 1800,

    /*
    | Specifies the partition name for que sessions
    */
    'partition' => APP_PACKAGE_NAME
];