<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/6/2020
 * Time: 5:50 PM
 */

namespace que\support;



class Obj
{

    /**
     * @param object $data
     * @return string
     */
    public static function to_string (object $data) {
        return object_to_string($data);
    }

    /**
     * @param object $object
     * @param array $keys
     * @return object
     */
    public static function replace_keys (object $object, array $keys) {
        return object_replace_keys($object, $keys);
    }

    /**
     * @param object $object
     * @param array $values
     * @return object
     */
    public static function replace_values (object $object, array $values) {
        return object_replace_values($object, $values);
    }

    /**
     * @param object $arr
     * @return object
     */
    public static function permutation (object $arr) {
        return object_permutation($arr);
    }

    /**
     * @param object $element
     * @param $callback
     * @param array $affected
     */
    public static function callback (object &$element, $callback, array $affected = []) {
        object_callback($element, $callback, $affected);
    }

    /**
     * @param object $arr
     * @return object
     */
    public static function make_key_from_value (object $arr) {
        return object_make_key_from_value($arr);
    }

    /**
     * @param object $main
     * @param mixed ...$exclude
     * @return object
     */
    public static function exclude (object $main, ...$exclude) {
        return object_exclude($main, ...$exclude);
    }

    /**
     * @param object $object
     * @param int $start
     * @param int $end
     * @return object
     */
    public static function extract (object $object, int $start, int $end) {
        return object_extract($object, $start, $end);
    }

    /**
     * @param object $object
     * @param array $keys
     * @return object
     */
    public static function extract_by_keys (object $object, array $keys) {
        return object_extract_by_keys($object, $keys);
    }

    /**
     * @param object $object
     * @param $from
     * @param $to
     * @return object
     */
    public static function rename_key (object $object, $from, $to) {
        $backup = [];
        foreach ($object as $key => $value) {
            $backup[$key] = $value;
            unset($object->{$key});
        }
        array_rename_key($backup, $from, $to);
        foreach ($backup as $key => $value)  $object->{$key} = $value;
        return $object;
    }

    /**
     * @param object $object1
     * @param object $object2
     * @return bool
     */
    public static function equal (object $object1, object $object2) {
        return object_equal($object1, $object2);
    }

    /**
     * @param object $object1
     * @param object $object2
     * @return bool
     */
    public static function identical (object $object1, object $object2) {
        return object_identical($object1, $object2);
    }

    /**
     * @param object $object
     * @return bool
     */
    public static function is_numeric (object $object) {
        return is_numeric_object($object);
    }

    /**
     * @param object $object
     * @return int
     */
    public static function size (object $object) {
        return object_size($object);
    }

    /**
     * @param object $object
     * @return array
     */
    public static function to_array (object $object) {
        return object_to_array($object);
    }

    /**
     * Determine if the given key exists in the provided object.
     *
     * @param $object
     * @param $key
     * @return bool
     */
    public static function exists($object, $key): bool
    {
        return object_key_exists($key, $object);
    }

    /**
     * Check if an item or items exist in an object using "dot" notation.
     *
     * @param  \ArrayAccess|object  $object
     * @param  string|object  $keys
     * @return bool
     */
    public static function has($object, $keys)
    {
        $keys = (array) $keys;

        if (! $object || $keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $subKeyObject = $object;

            if (static::exists($object, $key)) {
                continue;
            }

            foreach (explode('.', $key) as $segment) {
                if (is_object($subKeyObject) && static::exists($subKeyObject, $segment)) {
                    $subKeyObject = $subKeyObject->{$segment};
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param object $object
     * @param $offset
     * @return bool
     */
    public static function isset(object $object, $offset) {
        return object_key_exists($offset, $object);
    }

    /**
     * Remove one or many object items from a given object using "dot" notation.
     *
     * @param $object
     * @param $keys
     */
    public static function unset (object &$object, $keys)
    {
        $original = &$object;

        $keys = (array) $keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            // if the exact key exists in the top-level, remove it
            if (static::exists($object, $key)) {
                unset($object->{$key});
                continue;
            }

            $parts = explode('.', $key);

            // clean up before each pass
            $object = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($object->{$part}) && is_object($object->{$part})) {
                    $object = &$object->{$part};
                } else {
                    continue 2;
                }
            }

            unset($object->{array_shift($parts)});
        }
    }

    /**
     * Set an item on an object using dot notation.
     *
     * @param object $object
     * @param $key
     * @param null $default
     * @return object|mixed
     */
    public static function get ($object, $key, $default = null) {
        return object_get($object, $key, $default);
    }

    /**
     * Set an item on an object using dot notation.
     *
     * @param object $object
     * @param $key
     * @param $value
     * @return object|mixed
     */
    public static function set (object &$object, $key, $value) {
        return object_set($object, $key, $value);
    }

    /**
     * @param object $haystack
     * @param $needle
     * @param null $default
     * @return mixed|null
     */
    public static function find_in_object (object $haystack, $needle, $default = null) {
        return object_get($haystack, $needle, $default);
    }

}
