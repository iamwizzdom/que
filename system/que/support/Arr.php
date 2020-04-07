<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/6/2020
 * Time: 5:50 PM
 */

namespace que\support;


class Arr
{
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
     * @param array $exclude
     * @return array
     */
    public static function exclude (array $main, array $exclude = []) {
        return array_exclude($main, $exclude);
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
    public static function _isset(array $array, $offset) {
        return isset($array[$offset]);
    }

    /**
     * @param array $haystack
     * @param $needle
     * @param null $default
     * @return mixed|null
     */
    public static function find_in_array (array $haystack, $needle, $default = null) {
        return find_in_array($haystack, $needle, $default);
    }

}