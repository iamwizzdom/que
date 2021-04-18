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
     * Determine if a given string matches a given pattern.
     *
     * @param  string|array  $pattern
     * @param  string  $value
     * @return bool
     */
    public static function is($pattern, $value)
    {
        $patterns = Arr::wrap($pattern);

        if (empty($patterns)) {
            return false;
        }

        foreach ($patterns as $pattern) {
            // If the given value is an exact match we can of course return true right
            // from the beginning. Otherwise, we will translate asterisks and do an
            // actual pattern match against the two strings to see if they match.
            if ($pattern == $value) {
                return true;
            }

            $pattern = preg_quote($pattern, '#');

            // Asterisks are translated into zero-or-more regular expression wildcards
            // to make it convenient to check if the strings starts with the given
            // pattern such as "library/*", making any string check convenient.
            $pattern = str_replace('\*', '.*', $pattern);

            if (preg_match('#^'.$pattern.'\z#u', $value) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function contains (string $haystack, string $needle) {
        return str__contains($haystack, $needle);
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
     * @param $search
     * @param $replace
     * @param $subject
     * @return string|string[]
     */
    public static function replace_first ($search, $replace, $subject) {
        return str_replace_first($search, $replace, $subject);
    }

    /**
     * @param $search
     * @param $replace
     * @param $subject
     * @return string|string[]
     */
    public static function replace_last ($search, $replace, $subject) {
        return str_replace_last($search, $replace, $subject);
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
     * @param bool $case_insensitive
     * @return bool
     */
    public static function starts_with (string $haystack, string $needle, bool $case_insensitive = false) {
        return str__starts_with($haystack, $needle, $case_insensitive);
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @param bool $case_insensitive
     * @return bool
     */
    public static function ends_with (string $haystack, string $needle, bool $case_insensitive = false) {
        return str__ends_with($haystack, $needle, $case_insensitive);
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
     * Like parse_str(), but preserves dots in variable names.
     */
    public static function parse_query(string $query, bool $ignoreBrackets = false, string $separator = '&'): array
    {
        $q = [];

        foreach (explode($separator, $query) as $v) {
            if (false !== $i = strpos($v, "\0")) {
                $v = substr($v, 0, $i);
            }

            if (false === $i = strpos($v, '=')) {
                $k = urldecode($v);
                $v = '';
            } else {
                $k = urldecode(substr($v, 0, $i));
                $v = substr($v, $i);
            }

            if (false !== $i = strpos($k, "\0")) {
                $k = substr($k, 0, $i);
            }

            $k = ltrim($k, ' ');

            if ($ignoreBrackets) {
                $q[$k][] = urldecode(substr($v, 1));

                continue;
            }

            if (false === $i = strpos($k, '[')) {
                $q[] = bin2hex($k).$v;
            } else {
                $q[] = bin2hex(substr($k, 0, $i)).rawurlencode(substr($k, $i)).$v;
            }
        }

        if ($ignoreBrackets) {
            return $q;
        }

        parse_str(implode('&', $q), $q);

        $query = [];

        foreach ($q as $k => $v) {
            if (false !== $i = strpos($k, '_')) {
                $query[substr_replace($k, hex2bin(substr($k, 0, $i)).'[', 0, 1 + $i)] = $v;
            } else {
                $query[hex2bin($k)] = $v;
            }
        }

        return $query;
    }

    /**
     * @param string $string
     * @param array $params
     * @return array|string|string[]
     */
    public static function interpolate (string $string, array $params) {
        return str_interpolate($string, $params);
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
