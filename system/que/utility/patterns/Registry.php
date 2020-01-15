<?php


namespace que\utility\pattern;


class Registry
{
    /**
     * @var array
     */
    protected static $data = [];

    /**
     * @param $key
     * @param $value
     */
    public static function set($key, $value)
    {
        self::$data[$key] = $value;
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public static function get($key)
    {
        return self::$data[$key] ?? null;
    }

    /**
     * @param $key
     */
    final public static function remove($key)
    {
        if (array_key_exists($key, self::$data))
            unset(self::$data[$key]);
    }
}