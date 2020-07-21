<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/6/2020
 * Time: 6:19 PM
 */

namespace que\utility\hash;


class Hash
{

    /**
     * @param $hash
     * @param string $sep
     * @param int $space
     * @return string
     */
    public static function format_hash($hash, $sep = ":", $space = 4)
    {
        return wordwrap($hash, $space, $sep, true);
    }

    /**
     * @param $data
     * @return false|string|null
     */
    public static function bcrypt($data)
    {
        return password_hash($data, PASSWORD_BCRYPT);
    }

    /**
     * @param $data
     * @return false|string|null
     */
    public static function argon2i($data)
    {
        return password_hash($data, PASSWORD_ARGON2I);
    }

    /**
     * @param $data
     * @param string $algo
     * @return string
     */
    public static function sha($data, string $algo = 'SHA256')
    {
        return hash($algo, $data);
    }

    /**
     * @param $data
     * @param string $algo
     * @param string $key
     * @return string
     */
    public static function hmac($data, string $algo = 'SHA256', string $key = null)
    {
        return hash_hmac($algo, $data, $key !== null ? $key : config("auth.app.secret"));
    }
}