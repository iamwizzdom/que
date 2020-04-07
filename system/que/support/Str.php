<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/6/2020
 * Time: 3:21 PM
 */

namespace que\support;


use que\utility\random\RandomFloatInterface;
use que\utility\random\TimeSourceInterface;
use que\utility\random\ULID;
use que\utility\random\UUID;

class Str
{

    /**
     * @param int $length
     * @param bool $hex
     * @return bool|string
     */
    public static function uid (int $length = 32, bool $hex = true) {
        return unique_id($length, $hex);
    }

    /**
     * @param $namespace
     * @param $name
     * @return bool|string
     */
    public static function uuidv3 ($namespace, $name) {
        return UUID::v3($namespace, $name);
    }

    /**
     * @return string
     */
    public static function uuidv4 () {
        return UUID::v4();
    }

    /**
     * @param $namespace
     * @param $name
     * @return bool|string
     */
    public static function uuidv5 ($namespace, $name) {
        return UUID::v5($namespace, $name);
    }

    /**
     * @param TimeSourceInterface|null $ts
     * @param RandomFloatInterface|null $rf
     * @return ULID
     */
    public static function ulid (TimeSourceInterface $ts = null, RandomFloatInterface $rf = null) {
        return new ULID($ts, $rf);
    }

    /**
     * @param int $length
     * @return string
     */
    public static function random (int $length = 6) {
        return str_rand($length);
    }

    /**
     * @param string $string
     * @return array
     */
    public static function to_char_array (string $string) {
        return str_to_char_array($string);
    }

    /**
     * @param string $string
     * @return array
     */
    public static function to_word_array (string $string) {
        return str_to_word_array($string);
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function contains (string $haystack, string $needle) {
        return str_contains($haystack, $needle);
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function start_from (string $haystack, string $needle) {
        return str_start_from($haystack, $needle);
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function end_at (string $haystack, string $needle) {
        return str_end_at($haystack, $needle);
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function char_count (string $haystack, string $needle) {
        return str_char_count($haystack, $needle);
    }

    /**
     * @param string $string
     * @param int $length
     * @param string|null $ellipsis
     * @return string
     */
    public static function ellipsis (string $string, int $length = 50, string $ellipsis = null) {
        return str_ellipsis($string, $length, $ellipsis);
    }

    /**
     * @param string $string
     * @param string $needle
     * @return bool|string
     */
    public static function strip (string $string, string $needle) {
        return str_strip($string, $needle);
    }

    /**
     * @param string $string
     * @return string
     */
    public static function strip_whitespaces (string $string) {
        return str_strip_whitespaces($string);
    }

    /**
     * @param string $string
     * @return string
     */
    public static function strip_special_char (string $string) {
        return str_strip_special_char($string);
    }

    /**
     * @param string $string
     * @return string
     */
    public static function strip_spaces (string $string) {
        return str_strip_spaces($string);
    }

    /**
     * @param string $string
     * @return string
     */
    public static function strip_excess_whitespace (string $string) {
        return str_strip_excess_whitespace($string);
    }

    /**
     * @param string $string
     * @return string
     */
    public static function strip_non_alpha_numeric_spaces (string $string) {
        return str_strip_non_alpha_numeric_spaces($string);
    }

    /**
     * @param string $string
     * @return string
     */
    public static function strip_non_alpha_numeric (string $string) {
        return str_strip_non_alpha_numeric($string);
    }

    /**
     * @param string $string
     * @return string
     */
    public static function strip_non_numeric (string $string) {
        return str_strip_non_numeric($string);
    }

    /**
     * @param string $string
     * @return string
     */
    public static function strip_non_alpha (string $string) {
        return str_strip_non_alpha($string);
    }

    /**
     * @param string $string
     * @return string
     */
    public static function strip_non_alpha_space (string $string) {
        return str_strip_non_alpha_space($string);
    }

    /**
     * @param array $array
     * @param string $needle
     * @param int $option
     * @return array|bool|int|string
     */
    public static function pos_in_array (
        array $array, string $needle,
        int $option = STRPOS_IN_ARRAY_OPT_DEFAULT
    ) {
        return strpos_in_array($array, $needle, $option);
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function starts_with (string $haystack, string $needle) {
        return str_starts_with($haystack, $needle);
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function ends_with (string $haystack, string $needle) {
        return str_ends_with($haystack, $needle);
    }

    /**
     * @param string $string
     * @return mixed|string
     */
    public static function flatten (string $string) {
        return str_flatten($string);
    }

    /**
     * @param string $string
     * @param bool $returnAsArray
     * @return mixed
     */
    public static function unique_chars (string $string, bool $returnAsArray = true) {
        return str_unique_chars($string, $returnAsArray);
    }

    /**
     * @param string $string
     * @param string $needle
     * @return array
     */
    public static function tokenize (string $string, string $needle) {
        return str_tokenize($string, $needle);
    }

    /**
     * @param string $url
     * @return string
     */
    public static function filter_url (string $url) {
        return filter_url($url);
    }

    /**
     * @param string $email
     * @param int|null $option
     * @return array|bool|mixed|string
     */
    public static function filter_email (string $email, int $option = null) {
        return filter_email($email, $option);
    }
}