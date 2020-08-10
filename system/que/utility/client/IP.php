<?php

/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/28/2019
 * Time: 10:39 PM
 */

namespace que\utility\client;

abstract class IP
{

    /**
     * @return mixed
     */
    public static function simple()
    {
        return ($_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '');
    }

    /**
     * @return mixed|null
     */
    public static function real()
    {

        if (self::isValid(self::serverVar("HTTP_CLIENT_IP")))
            return self::serverVar("HTTP_CLIENT_IP");

        foreach (explode(",", self::serverVar("HTTP_X_FORWARDED_FOR")) as $ip)
            if (self::isValid(trim($ip))) return $ip;

        if (self::isValid(self::serverVar("HTTP_X_FORWARDED")))
            return self::serverVar("HTTP_X_FORWARDED");
        elseif (self::isValid(self::serverVar("HTTP_FORWARDED_FOR")))
            return self::serverVar("HTTP_FORWARDED_FOR");
        elseif (self::isValid(self::serverVar("HTTP_FORWARDED")))
            return self::serverVar("HTTP_FORWARDED");
        elseif (self::isValid(self::serverVar("HTTP_X_FORWARDED")))
            return self::serverVar("HTTP_X_FORWARDED");
        else return self::serverVar("REMOTE_ADDR");

    }

    /**
     * @param $key
     * @return mixed|null
     */
    public static function serverVar($key)
    {
        return server($key, '');
    }

    /**
     * Validating an IP address
     *
     * @param string $ip
     * @return bool
     */
    public static function isValid(string $ip)
    {
        if (!empty($ip) && ($long = ip2long($ip)) != -1) {
            $reserved_ips = [
                [
                    '0.0.0.0',
                    '2.255.255.255'
                ],
                [
                    '10.0.0.0',
                    '10.255.255.255'
                ],
                [
                    '127.0.0.0',
                    '127.255.255.255'
                ],
                [
                    '169.254.0.0',
                    '169.254.255.255'
                ],
                [
                    '172.16.0.0',
                    '172.31.255.255'
                ],
                [
                    '192.0.2.0',
                    '192.0.2.255'
                ],
                [
                    '255.255.255.0',
                    '255.255.255.255'
                ]
            ];

            foreach ($reserved_ips as $r) {
                $min = ip2long($r[0]);
                $max = ip2long($r[1]);
                if (($long >= $min) && ($long <= $max))
                    return false;
            }
            return true;

        } else {
            return false;
        }
    }

    /**
     * Validating an IPv4 IP address
     *
     * @param string $ip
     * @return bool
     */
    public static function isIpv4(string $ip)
    {
        return self::isValid($ip) && (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false);
    }

    /**
     * Validating an IPv4 IP address, excluding private range addresses
     *
     * @param string $ip
     * @return bool
     */
    public static function isIpv4NoPriv(string $ip)
    {
        return self::isValid($ip) && (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE) !== false);
    }

    /**
     * Validating an IPv6 IP address
     *
     * @param string $ip
     * @return bool
     */
    public static function isIpv6(string $ip)
    {
        return self::isValid($ip) && (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false);
    }

    /**
     * Validating an IPv6 IP address, excluding private range addresses
     *
     * @param string $ip
     * @return bool
     */
    public static function isIpv6NoPriv(string $ip)
    {
        return self::isValid($ip) && (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE) !== false);
    }
}

