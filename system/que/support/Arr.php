<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/6/2020
 * Time: 5:50 PM
 */

namespace que\support;


use ArrayAccess;

class Arr
{

    /**
     * Determine whether the given value is array accessible.
     *
     * @param $value
     * @return bool
     */
    public static function is_accessible($value): bool
    {
        return array_is_accessible($value);
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param $array
     * @param $key
     * @return bool
     */
    public static function exists($array, $key): bool
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    /**
     * Add an element to an array using "dot" notation if it doesn't exist.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    public static function add($array, $key, $value)
    {
        if (is_null(static::get($array, $key))) {
            static::set($array, $key, $value);
        }

        return $array;
    }

    /**
     * Check if an item or items exist in an array using "dot" notation.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string|array  $keys
     * @return bool
     */
    public static function has($array, $keys)
    {
        $keys = (array) $keys;

        if (! $array || $keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $subKeyArray = $array;

            if (static::exists($array, $key)) {
                continue;
            }

            foreach (explode('.', $key) as $segment) {
                if (static::is_accessible($subKeyArray) && static::exists($subKeyArray, $segment)) {
                    $subKeyArray = $subKeyArray[$segment];
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Determines if an array is associative.
     *
     * An array is "associative" if it doesn't have sequential numerical keys beginning with zero.
     *
     * @param  array  $array
     * @return bool
     */
    public static function isAssoc(array $array)
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }

    /**
     * Collapse a multi-dimensional array to a single list array
     * @param array $array
     * @return array
     */
    public static function collapse(array $array) {
        return array_collapse($array);
    }

    /**
     * Get a value from the array, and remove it.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public static function pull(&$array, $key, $default = null)
    {
        $value = static::get($array, $key, $default);

        static::unset($array, $key);

        return $value;
    }

    /**
     * @param array $data
     * @return string
     */
    public static function serializer (array $data) {
        return serializer($data);
    }

    /**
     * @param array $data
     * @return string
     */
    public static function to_string (array $data) {
        return array_to_string($data);
    }

    /**
     * @param array $array
     * @param array $keys
     * @param bool $strict
     * @return array
     */
    public static function replace_keys (array $array, array $keys, bool $strict = false) {
        return array_replace_keys($array, $keys, $strict);
    }

    /**
     * @param array $array
     * @param array $values
     * @param bool $strict
     * @return array
     */
    public static function replace_values (array $array, array $values, bool $strict = false) {
        return array_replace_values($array, $values, $strict);
    }

    /**
     * @param array $arr
     * @return array
     */
    public static function permutation (array $arr) {
        return array_permutation($arr);
    }

    /**
     * @param array $element
     * @param $callback
     * @param array $affected
     */
    public static function callback (array &$element, $callback, array $affected = []) {
        array_callback($element, $callback, $affected);
    }

    /**
     * @param array $arr
     * @return array
     */
    public static function make_key_from_value (array $arr) {
        return array_make_key_from_value($arr);
    }

    /**
     * @param array $main
     * @param string ...$exclude
     * @return array
     */
    public static function exclude (array $main, ...$exclude) {
        return array_exclude($main, ...$exclude);
    }

    /**
     * @param array $array
     * @param int $start
     * @param int $end
     * @return array
     */
    public static function extract (array $array, int $start, int $end) {
        return array_extract($array, $start, $end);
    }

    /**
     * @param array $array
     * @param array $keys
     * @return array
     */
    public static function extract_by_keys (array $array, array $keys) {
        return array_extract_by_keys($array, $keys);
    }

    /**
     * @param array $array1
     * @param array $array2
     * @return bool
     */
    public static function equal (array $array1, array $array2) {
        return array_equal($array1, $array2);
    }

    /**
     * @param array $array1
     * @param array $array2
     * @return bool
     */
    public static function identical (array $array1, array $array2) {
        return array_identical($array1, $array2);
    }

    /**
     * @param array $array
     * @return bool
     */
    public static function is_numeric (array $array) {
        return is_numeric_array($array);
    }

    /**
     * @param array $array
     * @return int
     */
    public static function size (array $array) {
        return array_size($array);
    }

    /**
     * @param array $array
     * @return object
     */
    public static function to_object (array $array) {
        return array_to_object($array);
    }

    /**
     * @param $value
     * @param int $range
     * @return array
     */
    public static function multi ($value, int $range) {
        return array_multi($value, $range);
    }

    /**
     * @param array $array
     * @param $offset
     * @return bool
     */
    public static function isset(array $array, $offset) {
        return array_has_key($array, $offset);
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param $array
     * @param $keys
     */
    public static function unset (array &$array, $keys)
    {
        $original = &$array;

        $keys = (array) $keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            // if the exact key exists in the top-level, remove it
            if (static::exists($array, $key)) {
                unset($array[$key]);
                continue;
            }

            $parts = explode('.', $key);

            // clean up before each pass
            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }

    /**
     * Set an item on an array using dot notation.
     *
     * @param array|object $array
     * @param $key
     * @param null $default
     * @return array|mixed
     */
    public static function get ($array, $key, $default = null) {

        if (!static::is_accessible($array)) {
            return value($default);
        }

        if (is_null($key)) {
            return $array;
        }

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        if (strpos($key, '.') === false) {
            return $array[$key] ?? value($default);
        }

        foreach (explode('.', $key) as $segment) {
            if (static::is_accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return value($default);
            }
        }

        return $array;
    }

    /**
     * Set an item on an array using dot notation.
     *
     * @param array $array
     * @param $key
     * @param $value
     * @return array|mixed
     */
    public static function set (array &$array, $key, $value) {
        return array_set($array, $key, $value);
    }

    /**
     * Convert the array into a query string.
     *
     * @param  array  $array
     * @return string
     */
    public static function to_http_query($array)
    {
        return http_build_query($array, null, '&', PHP_QUERY_RFC3986);
    }

    /**
     * Filter the array using the given callback.
     *
     * @param  array  $array
     * @param  callable  $callback
     * @return array
     */
    public static function where($array, callable $callback)
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

}
