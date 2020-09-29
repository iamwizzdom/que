<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 27/7/2019
 * Time: 12:21 AM
 */

namespace que\security\jwt;

/**
 * Base64 url encode and decode implementation
 *
 */
class Base64Url
{

    /**
     * @param $data
     * @return string
     */
    public static function encode($data) : string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * @param $data
     * @return string
     */
    public static function decode($data) : string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
