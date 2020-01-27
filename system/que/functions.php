<?php

use que\common\time\Time;
use que\common\validate\Track;
use que\database\mysql\Query;
use que\error\RuntimeError;
use que\http\Http;
use que\route\Route;
use que\route\structure\RouteEntry;
use que\security\CSRF;
use que\template\Composer;
use que\template\Form;
use que\template\Pagination;
use que\user\User;
use que\utility\Converter;

/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/10/2018
 * Time: 3:55 PM
 */


/**
 * debug_print is used to output all data types
 * @param mixed ...$params
 * @return string
 */
function debug_print(...$params)
{
    $args = func_get_args();

    $end = end($args);

    if ($end === true) array_pop($args);

    $print = "<pre>";
    foreach ($args as $arg) {
        $print .= print_r($arg, true);
        $print .= "\t";
    }
    $print .= "</pre>";

    if ($end === true) return $print;
    else echo $print;
}


/**
 * String functions starts here
 */

/**
 * @param string $number
 * @param int $start
 * @param int $end
 * @return string
 */
function hide_number(string $number, int $start = 0, int $end = 1): string
{
    if (empty($number)) return '';
    $hidden = '';
    $size = strlen($number);
    for ($i = 0; $i < $size; $i++) $hidden .= ($i >= $start &&
    (is_numeric($end) ? $i < $end : $i < $size) ? '*' : substr($number, $i, 1));
    return $hidden;
}

/**
 * @param string $number
 * @return mixed
 */
function filter_number(string $number): string
{
    return preg_replace("/[^+0-9]/", "", $number);
}

/**
 * @param string $phone
 * @param $prefix
 * @return string
 */
function format_phone(string $phone, $prefix): string
{
    $phone = filter_number($phone);
    if (str_starts_with($phone, '+')) return $phone;
    elseif (str_starts_with($phone, $prefix))
        return str_starts_with($prefix, '+') ? $phone : "+{$phone}";
    return (str_starts_with($prefix, '+') ? '' : '+') . ($prefix . substr($phone, 1, strlen($phone)));
}

/**
 * @param int $length
 * @param bool $hex
 * @return bool|string
 */
function unique_id(int $length = 32, bool $hex = true)
{
    if (function_exists("openssl_random_pseudo_bytes")) {
        $r = openssl_random_pseudo_bytes($length);
    } else if (is_readable('/dev/urandom')) {
        $r = file_get_contents('/dev/urandom', false, null, 0, $length);
    } else {
        $i = 0;
        $r = "";
        while ($i++ < $length) $r .= chr(mt_rand(0, 255));
    }

    return substr($hex ? bin2hex($r) : $r, 0, $length);
}

/**
 * @param int $length
 * @return string
 */
function str_rand(int $length = 6): string
{
    $string = str_shuffle('234ABCDEFGHIJK789LM*NOPQRabcdeSTUV$WXYZfghijkl&@mnop56qrs%tuv01wxyz');
    $size = strlen($string);
    $random = "";
    for ($i = 0; $i < $length; $i++)
        $random .= substr($string, mt_rand(0, ($size - 1)), 1);
    return $random;
}

/**
 * @param string $string
 * @return array
 */
function str_to_char_array(string $string): array
{
    $size = strlen($string);
    $array = [];
    for ($i = 0; $i < $size; $i++) {
        $fragment = substr($string, $i, 1);
        if ($fragment == '0' || !empty($fragment))
            $array[] = $fragment;
    }
    return $array;
}

/**
 * @param string $string
 * @return array
 */
function str_to_word_array(string $string): array
{
    $words = explode(" ", $string);
    $array = [];
    foreach ($words as $word)
        if ($word == '0' || !empty($word))
            $array[] = $word;
    return $array;
}

/**
 * @param string $haystack
 * @param string $needle
 * @return bool
 */
function str_contains(string $haystack, string $needle): bool
{
    return strpos($haystack, $needle) !== false;
}

/**
 * This function will return a substring of
 * @param string $haystack
 * starting from the first occurrence of
 * @param string $needle
 * @return bool|string
 */
function str_start_from(string $haystack, string $needle) {
    if (($pos = strpos($haystack, $needle)) === false) return $haystack;
    return substr($haystack, $pos = ($pos + strlen($needle)), (strlen($haystack) - $pos));
}

/**
 * This function will return a substring of
 * @param string $haystack
 * ending at the first occurrence of
 * @param string $needle
 * @return bool|string
 */
function str_end_at(string $haystack, string $needle) {
    if (($pos = strpos($haystack, $needle)) === false) return $haystack;
    return substr($haystack, 0, ($pos + strlen($needle)));
}

/**
 * @param string $string
 * @param string $needle
 * @return int
 */
function str_char_count(string $string, string $needle): int
{
    $count = 0;
    while (($pos = strpos($string, $needle)) !== false) {
        $string = substr($string, ($pos + 1));
        $count++;
    }
    return $count;
}

/**
 * @param string $string
 * @param int $length
 * @param string|null $ellipsis
 * @return string
 */
function str_ellipsis(string $string, int $length = 50, string $ellipsis = null): string
{
    $size = strlen($string);
    $string = substr($string, 0, ($size > $length ? $length : $size));
    $end = strrpos($string, " ");
    $end_pos = ($size > $length ? ($end !== false ? $end : $length) : $size);
    return substr($string, 0, $end_pos) . ($end_pos < $size ? (is_null($ellipsis) ? "..." : $ellipsis) : "");
}

/**
 * @param string $string
 * @param string $needle
 * @return bool|string
 */
function str_strip(string $string, string $needle) {
    if (($pos = strpos($string, $needle)) === false) return $string;
    return preg_replace("[{$needle}]", "", $string);
}

/**
 * @param string $string
 * @return string
 */
function str_strip_whitespaces(string $string): string
{
    return preg_replace("/\s+/", " ", $string);
}

/**
 * @param string $string
 * @return string
 */
function str_strip_special_char(string $string): string
{
    return preg_replace("/[^a-zA-Z0-9 ]/", "", $string);
}

/**
 * @param string $string
 * @return string
 */
function str_strip_spaces(string $string): string
{
    return str_replace(" ", "", $string);
}

/**
 * Finds the key and position of the first occurrence of a substring in an array
 *
 * @param array $array
 * @param string $needle
 * @param int $option | -1 will return an array of both the array index and string position of the needle,
 * 0 will return the array index of the needle, while 1 will return the string position of the needle.
 * @return array|bool|int|string
 */
function strpos_in_array(
    array $array, string $needle,
    int $option = STRPOS_IN_ARRAY_OPT_DEFAULT
) {
    foreach ($array as $index => $value) {
        $position = strpos($value, $needle);
        if ($position !== false) {
            switch ($option) {
                case STRPOS_IN_ARRAY_OPT_DEFAULT:
                    return [
                        STRPOS_IN_ARRAY_OPT_ARRAY_INDEX => $index,
                        STRPOS_IN_ARRAY_OPT_STR_POSITION => $position
                    ];
                case STRPOS_IN_ARRAY_OPT_ARRAY_INDEX:
                    return $index;
                case STRPOS_IN_ARRAY_OPT_STR_POSITION:
                    return $position;
                default:
                    return [
                        STRPOS_IN_ARRAY_OPT_ARRAY_INDEX => $index,
                        STRPOS_IN_ARRAY_OPT_STR_POSITION => $position
                    ];
            }
        }
    }
    return false;
}

/**
 * @param string $haystack
 * @param string $needle
 * @return bool
 */
function str_starts_with(string $haystack, string $needle): bool
{
    return strcmp(substr($haystack, 0, strlen($needle)), $needle) == 0;
}

/**
 * @param string $haystack
 * @param string $needle
 * @return bool
 */
function str_ends_with(string $haystack, string $needle): bool
{
    return strcmp(substr($haystack, (strlen($haystack) - ($len = strlen($needle))), $len), $needle) == 0;
}

/**
 *
 * Removes line breaks from a string
 *
 * @param string $string
 * @return mixed|string
 */
function str_flatten(string $string): string
{
    $string = str_replace("\n", "", $string);
    $string = str_replace("\t", "", $string);
    $string = str_replace("\r", "", $string);
    return $string;
}

/**
 * Get an array of unique characters used in a string.
 * This should also work with multibyte characters.
 *
 * @param string $string
 * @param bool $returnAsArray
 * @return mixed
 */
function str_unique_chars(string $string, bool $returnAsArray = true)
{
    $unique = array_unique(preg_split('/(?<!^)(?!$)/u', $string));
    if (!$returnAsArray) $unique = implode("", $unique);
    return $unique;
}

/**
 * Transform two or more spaces into just one space.
 *
 * @param string $string
 * @return string
 */
function str_strip_excess_whitespace(string $string): string
{
    return preg_replace('/  +/', ' ', $string);
}

/**
 * Remove all characters except letters, numbers, and spaces.
 *
 * @param string $string
 * @return string
 */
function str_strip_non_alpha_numeric_spaces(string $string): string
{
    return preg_replace("/[^a-z0-9 ]/i", "", $string);
}

/**
 * Remove all characters except letters and numbers.
 *
 * @param string $string
 * @return string
 */
function str_strip_non_alpha_numeric(string $string): string
{
    return preg_replace("/[^a-z0-9]/i", "", $string);
}

/**
 * Remove all characters except numbers.
 *
 * @param string $string
 * @return string
 */
function str_strip_non_numeric(string $string): string
{
    return preg_replace("/[^0-9]/", "", $string);
}

/**
 * Remove all characters except letters.
 *
 * @param string $string
 * @return string
 */
function str_strip_non_alpha(string $string): string
{
    return preg_replace("/[^a-z]/i", "", $string);
}

/**
 * Remove all characters except letters and spaces
 *
 * @param string $name
 * @return string
 */
function str_strip_non_alpha_space(string $name): string
{
    return preg_replace("/[^a-z A-Z]/", "", $name);
}

/**
 * @param string $string
 * @param string $needle
 * @return array
 */
function str_tokenize(string $string, string $needle): array {

    $tokens = [];

    $str_arr = explode($needle, $string);

    foreach ($str_arr as $token)
        if ($token == "0" || !empty($token))
            $tokens[] = $token;

    return $tokens;
}

/**
 * Remove all characters except from url except domain name and uri
 *
 * @param string $url
 * @return string
 */
function filter_url(string $url): string
{
    $url = str_replace("http://", "", $url);
    $url = str_replace("https://", "", $url);
    $url = str_replace("/", "", $url);
    return $url;
}

/**
 * @param string $email
 * @param int|null $option
 * @return bool|string
 */
function filter_email(string $email, int $option = null)
{

    if (!is_email($email)) return false;

    if ($option !== null) {
        $email_arr = explode("@", $email);
        switch ($option) {
            case FILTER_GET_EMAIL_NAME:
                return $email_arr[FILTER_GET_EMAIL_NAME];
            case FILTER_GET_EMAIL_HOST:
                return $email_arr[FILTER_GET_EMAIL_HOST];
            default:
                return false;
        }
    }

    return trim($email);
}

/**
 * @param string $format
 * @param $date
 * @return bool
 */
function is_date(string $format, string $date): bool {
    return date($format, (int) strtotime($date)) == $date;
}

/**
 * @param string $format
 * @param string $date
 * @param string $default
 * @return string
 */
function get_date(string $format, string $date, string $default = ''): string {
    try {
        $dateTime = new DateTime($date);
    } catch (Exception $e) {
        $dateTime = false;
    }
    return !$dateTime ? $default : $dateTime->format($format);
}

function get_bearer_token() {
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        if (preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            return $matches[1];
        }
    }
    return null;
}

/**
 * String functions ends here
 */


/**
 * Number functions starts here
 */


/**
 * @param int $input
 * @return string
 */
function number_short(int $input): string
{
    $k = pow(10, 3);
    $mil = pow(10, 6);
    $bil = pow(10, 9);
    $tril = pow(10, 12);
    $quad = pow(10, 15);
    $quint = pow(10, 18);

    if ($input >= $quint)
        return number_format((int)($input / $quint)) . 'quint';
    elseif ($input >= $quad)
        return number_format((int)($input / $quad)) . 'quad';
    elseif ($input >= $tril)
        return number_format((int)($input / $tril)) . 'tril';
    elseif ($input >= $bil)
        return number_format((int)($input / $bil)) . 'bil';
    elseif ($input >= $mil)
        return number_format((int)($input / $mil)) . 'mil';
    elseif ($input >= $k)
        return number_format((int)($input / $k)) . 'k';
    else return number_format((int)$input);
}

/**
 * @param int $value
 * @param int $total
 * @param int $decimal
 * @return string
 */
function number_percent(int $value, int $total, int $decimal = 2): string
{
    if ($value == 0 || $total == 0) return '0';
    return number_format(($value / $total) * 100, $decimal);
}

/**
 * @param int $num
 * @return bool|mixed|null|string
 */
function number_to_word(int $num)
{
    $string = '';

    try {

        // Clean Number
        $num = preg_replace('/[^0-9.]+/', '', $num);

        $hyphen = '-';
        $conjunction = ' and ';
        $separator = ', ';
        $negative = 'negative ';
        $decimal = ' point ';
        $dictionary = array(
            0 => 'zero',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
            20 => 'twenty',
            30 => 'thirty',
            40 => 'fourty',
            50 => 'fifty',
            60 => 'sixty',
            70 => 'seventy',
            80 => 'eighty',
            90 => 'ninety',
            100 => 'hundred',
            1000 => 'thousand',
            '1000000' => 'million',
            '1000000000' => 'billion',
            '1000000000000' => 'trillion',
            '1000000000000000' => 'quadrillion',
            '1000000000000000000' => 'quintillion'
        );

        if (!is_numeric($num)) return false;

        if ((int)$num < PHP_INT_MIN || (int)$num > PHP_INT_MAX) {
            // overflow
            throw new Exception('number_to_word only accepts numbers between ' .
                PHP_INT_MIN . ' and ' . PHP_INT_MAX);
        }

        if ($num < 0) return $negative . number_to_word(abs($num));

        $fraction = null;

        if (strpos($num, '.') !== false) list($num, $fraction) = explode('.', $num);

        switch (true) {
            case $num < 21:
                $string = $dictionary[$num];

                break;
            case $num < 100:
                $tens = ((int)($num / 10)) * 10;
                $units = $num % 10;
                $string = $dictionary[$tens];
                if ($units) $string .= $hyphen . $dictionary[$units];
                break;
            case $num < 1000:
                $hundreds = $num / 100;
                $remainder = $num % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) $string .= $conjunction . number_to_word($remainder);
                break;
            default:

                $baseUnit = bcpow(1000, floor(log($num, 1000)));

                $numBaseUnits = bcdiv($num, $baseUnit);
                $remainder = bcmod($num, $baseUnit);

                $string = number_to_word($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= number_to_word($remainder);
                }

                break;
        }

        $fraction = rtrim((string)$fraction, "0");
        if (!empty($fraction) && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string)$fraction) as $num)
                $words[] = $dictionary[$num];
            $string .= implode(' ', $words);
        }

    } catch (Exception $e) {

        RuntimeError::render(E_USER_ERROR, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(),
            "Function Error (Func::number_to_word)");
    }

    return $string;
}

/**
 * @param int $num
 * @param int $nearest
 * @return float|int
 */
function number_round(int $num, int $nearest = 100)
{
    return ceil($num / $nearest) * $nearest;
}

/**
 * @param int $bytes
 * @param int $decimals
 * @return string
 */
function convert_bytes(int $bytes, int $decimals = 0): string
{
    $bytes = intval($bytes);
    if ($bytes === 0) return '0 Bytes';
    $k = 1024;
    $decimals = $decimals || 2;
    $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    $i = intval(floor(log($bytes) / log($k)));
    return round(($bytes / pow($k, $i)), $decimals) . $sizes[$i];
}

/**
 * @param int $mega_bytes
 * @param int $decimals
 * @return float
 */
function convert_mega_bytes(int $mega_bytes, int $decimals = 0): float
{
    $k = (1024 * 1024);
    $decimals = $decimals || 2;
    return round(($mega_bytes * $k), $decimals);
}

/**
 * Number functions ends here
 */


/**
 * Array functions starts here
 */


/**
 * @param array $data
 * @return string
 */
function serializer(array $data): string
{
    $serial = [];
    foreach ($data as $key => $value) {
        $serial[] = "{$key}={$value}";
    }
    return join('&', $serial);
}

/**
 * @param array $array
 * @return string
 */
function array_to_string(array $array): string
{
    return @json_encode($array, JSON_PRETTY_PRINT);
}

/**
 * @param array $array
 * @param array $keys [This should be an associative array, in which the indexes
 * are the original indexes to be replaced and the values is the new index to be replaced with]
 * @param bool $strict [When this is true, all keys in the array will be replaced with the new keys regardless of a match in their indexes]
 * @return array
 */
function array_replace_keys(array $array, array $keys, bool $strict = false): array
{
    if ($strict === true) {

        $m = array_keys($array); $n = array_keys($keys);

        for ($i = 0; $i < count($keys) && $i < count($array); $i++) {
            $array[$keys[$n[$i]]] = is_callable($array[$m[$i]]) ? $array[$m[$i]]() : $array[$m[$i]];
            unset($array[$m[$i]]);
        }

    } else {
        foreach ($keys as $key => $value) {
            if (array_key_exists($key, $array)) {
                $array[$value] = is_callable($array[$key]) ? $array[$key]() : $array[$key];
                unset($array[$key]);
            }
        }
    }
    return $array;
}

/**
 * @param array $array
 * @param array $values [This should be an associative array, in which the indexes
 * are the original indexes to be replaced and the values is the new values to be replaced with]
 * @param bool $strict [When this is true, all values in the array will be replaced with the new values regardless of a match in their indexes]
 * @return array
 */
function array_replace_values(array $array, array $values, bool $strict = false): array
{
    if ($strict === true) {

        $n = array_keys($array); $m = array_keys($values);

        for ($i = 0; $i < count($values) && $i < count($array); $i++)
            $array[$n[$i]] = is_callable($values[$m[$i]]) ? $values[$m[$i]]() : $values[$m[$i]];

    } else {
        foreach ($values as $key => $value)
            if (array_key_exists($key, $array))
                $array[$key] = is_callable($value) ? $value() : $value;
    }
    return $array;
}

/**
 * @param array $arr
 * @return array
 */
function array_permutation(array $arr): array
{
    $list = [];
    while ($ele = array_shift($arr)) {
        $x = [$ele];
        $list[] = $x;
        foreach ($arr as $rest) {
            $x[] = $rest;
            $list[] = $x;
        }
    }
    return $list;
}

/**
 * @param array $element
 * @param $callback
 * @param array $affected
 */
function array_callback(array &$element, $callback, array $affected = [])
{
    if (!empty($affected)) {
        foreach ($affected as $key)
            if (array_key_exists($key, $element))
                $element[$key] = call_user_func($callback, $element[$key], $key, $element);
        return;
    }
    foreach ($element as $key => $value)
        $element[$key] = call_user_func($callback, $value, $key, $element);
}

/**
 * @param $array
 * @return array
 */
function array_make_key_from_value(array $array): array
{
    foreach ($array as $key => $value) {
        $array[$value] = $value;
        unset($array[$key]);
    }
    return $array;
}

/**
 * @param array $main | Array being reduced
 * @param array $exclude | Keys to be excluded
 * @return array
 */
function array_exclude(array $main, array $exclude = []): array
{
    foreach ($exclude as $value)
        if (array_key_exists($value, $main)) unset($main[$value]);
    return $main;
}

/**
 * Note that this function does not support associative or multi-dimensional arrays
 *
 * @param array $array | Array to extract from
 * @param int $start | Extraction starting point
 * @param int $end | Extraction ending point
 * @return array
 */
function array_extract(array $array, int $start, int $end): array
{
    $extracted = [];
    $size = count($array); $keys = array_keys($array);
    for ($i = $start; $i < $size; $i++) {
        $extracted[$keys[$i]] = $array[$keys[$i]];
        if ($i == $end) break;
    }
    return $extracted;
}

/**
 * @param array $array | Array to extract from
 * @param array $keys | Key to extracted
 * @return array
 */
function array_extract_by_keys(array $array, array $keys): array
{
    $extracted = [];

    foreach ($keys as $key => $value) {

        if (is_array($value)) {

            if (array_key_exists($key, $array) && is_array($array[$key]))
                $extracted[$key] = array_extract_by_keys($array[$key], $value);

        } elseif (array_key_exists($value, $array)) {
            $extracted[$value] = $array[$value];
        }

    }
    return $extracted;
}

/**
 * @param array $array1
 * @param array $array2
 * @return bool
 */
function array_equal(array $array1, array $array2): bool
{
    if (array_size($array1) != array_size($array2)) return false;
    foreach ($array1 as $value)
        if (!in_array($value, $array2)) return false;
    return true;
}

/**
 * @param array $array1
 * @param array $array2
 * @return bool
 */
function array_identical(array $array1, array $array2): bool
{
    if (array_size($array1) != array_size($array2)) return false;
    foreach ($array1 as $value)
        if (!in_array($value, $array2, true))
            return false;
    return true;
}

/**
 * @param array $array
 * @return bool
 */
function is_numeric_array(array $array): bool {
    foreach ($array as $value)
        if (!is_numeric($value)) return false;
    return true;
}

/**
 * @param array $arr
 * @return int
 */
function array_size(array $arr): int
{
    return count($arr);
}

/**
 * @param array $array
 * @return object
 */
function array_to_object(array $array): object {
    return (object) $array;
}

/**
 * @param $value
 * @param int $range
 * @return array
 */
function array_multi($value, int $range) {
    $list = [];
    for ($i = 0; $i < $range; $i++)
        $list[] = $value;
    return $list;
}

/**
 * Array functions ends here
 */






/**
 * Object functions starts here
 */


/**
 * @param object $object
 * @return int
 */
function object_size(object $object): int
{
    return array_size(get_object_vars($object)) + array_size(get_class_methods($object));
}

/**
 * @param object $object
 * @return array
 */
function object_to_array(object $object) {
    return (array) $object;
}

/**
 * @param object $object
 * @return object
 */
function object_keys(object $object): object {
    $keys = [];
    foreach ($object as $key => $value)
        $keys[] = $key;
    return (object) $keys;
}

/**
 * @param object $object
 * @return object
 */
function object_values(object $object): object {
    $values = [];
    foreach ($object as $value)
        $values[] = $value;
    return (object) $values;
}

/**
 * @param $needle
 * @param object $object
 * @param bool $strict
 * @return bool
 */
function in_object($needle, object $object, bool $strict = false): bool {
    foreach ($object as $value) {
        if ($strict === true) {
            if ($value === $needle) return true;
        } elseif ($value == $needle) return true;
    }
    return false;
}

/**
 * @param object $object
 * @return bool
 */
function is_numeric_object(object $object): bool {
    foreach ($object as $value)
        if (!is_numeric($value)) return false;
    return true;
}

/**
 * @param object $object
 * @return string
 */
function object_to_string(object $object): string
{
    return @json_encode((array) $object, JSON_PRETTY_PRINT);
}

/**
 * @param object $object
 * @param array $keys [This should be an associative object, in which the indexes
 * are the original indexes to be replaced and the values is the new index to be replaced with]
 * @return object
 */
function object_replace_keys(object $object, array $keys): object
{
    foreach ($keys as $key => $value) {
        if (isset($object->{$key})) {
            $object->{$value} = $object->{$key};
            unset($object->{$key});
        }
    }
    return $object;
}

/**
 * @param object $object
 * @param array $values [This should be an associative object, in which the indexes
 * are the original indexes to be replaced and the values is the new values to be replaced with]
 * @return object
 */
function object_replace_values(object $object, array $values): object
{
    foreach ($values as $key => $value)
        if (isset($object->{$key}))
            $object->{$key} = $value;
    return $object;
}

/**
 * @param object $object
 * @return object
 */
function object_permutation(object $object): object
{
    $list = [];
    foreach ($object as $key => $value) {
        $x = [$value];
        $list[] = $x;
        unset($object->{$key});
        foreach ($object as $_key => $rest) {
            $x[] = $rest;
            $list[] = $x;
        }
    }
    return (object) $list;
}

/**
 * @param object $element
 * @param $callback
 * @param array $affected
 */
function object_callback(object &$element, $callback, array $affected = [])
{
    if (!empty($affected)) {
        foreach ($affected as $key)
            if (isset($element->{$key}))
                $element->{$key} = call_user_func($callback, $element->{$key}, $key, $element);
        return;
    }
    foreach ($element as $key => $value)
        $element->{$key} = call_user_func($callback, $value, $key, $element);
}

/**
 * @param $object
 * @return object
 */
function object_make_key_from_value(object $object): object
{
    foreach ($object as $key => $value) {
        $object->{$value} = $value;
        unset($object->{$key});
    }
    return $object;
}

/**
 * @param object $main | Object being reduced
 * @param array $exclude | Keys to be excluded
 * @return object
 */
function object_exclude(object $main, array $exclude = []): object
{
    foreach ($exclude as $value)
        if (isset($main->{$value})) unset($main->{$value});
    return $main;
}

/**
 * Note that this function does not support associative or multi-dimensional objects
 *
 * @param object $object | Object to extract from
 * @param int $start | Extraction starting point
 * @param int $end | Extraction ending point
 * @return object
 */
function object_extract(object $object, int $start, int $end): object
{
    $extracted = [];
    $size = object_size($object); $keys = object_keys($object);
    for ($i = $start; $i < $size; $i++) {
        $extracted[] = $object->{$keys[$i]};
        if ($i == $end) break;
    }
    return (object) $extracted;
}

/**
 * @param object $object | Object to extract from
 * @param array $keys | Key to extracted
 * @return object
 */
function object_extract_by_key(object $object, array $keys): object
{
    $extracted = new stdClass();
    foreach ($keys as $key)
        if (isset($object->{$key}))
            $extracted->{$key} = $object->{$key};
    return $extracted;
}

/**
 * @param object $object1
 * @param object $object2
 * @return bool
 */
function object_equal(object $object1, object $object2): bool
{
    if (object_size($object1) != object_size($object2)) return false;
    foreach ($object1 as $value)
        if (!in_object($value, $object2)) return false;
    return true;
}

/**
 * @param object $object1
 * @param object $object2
 * @return bool
 */
function object_identical(object $object1, object $object2): bool
{
    if (object_size($object1) != object_size($object2)) return false;
    foreach ($object1 as $value)
        if (!in_object($value, $object2, true))
            return false;
    return true;
}

/**
 * Object functions ends here
 */






/**
 * Misc functions starts here
 */

/**
 * @param string $json
 * @return int
 */
function json_size(string $json): int
{
    $decode = json_decode($json, true);
    return !$decode ? 0 : array_size($decode);
}


/**
 * @param string $email
 * @return bool
 */
function is_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * @return Converter
 */
function converter()
{
    return Converter::getInstance();
}

/**
 * @return Time
 */
function _time()
{
    return Time::getInstance();
}

/**
 * @param string $date
 * @return int
 */
function convert_date_to_age(string $date): int
{
    $age = 0;
    try {
        $date = new DateTime($date);
        $to = new DateTime('today');
        $age = (int) $date->diff($to)->y;;
    } catch (Exception $e) {

        RuntimeError::render(E_USER_ERROR, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(),
            "Function Error (Func::convert_date_to_age)");
    }
    return $age;
}


/**
 * @param null $param
 * @return mixed|null
 */
function post($param = null)
{

    if (is_null($param)) return \http()->_post()->_get();

    return \http()->_post()->get($param);
}

/**
 * @param null $param
 * @return mixed|null
 */
function get($param = null)
{

    if (is_null($param)) return \http()->_get()->_get();

    return \http()->_get()->get($param);
}

/**
 * @param null $param
 * @return mixed|null
 */
function server($param = null)
{

    if (is_null($param)) return \http()->_server()->_get();

    return \http()->_server()->get($param);
}

/**
 * @param null $param
 * @return mixed|null
 */
function request($param = null)
{

    if (is_null($param)) return \http()->_request()->_get();

    return \http()->_request()->get($param);
}

/**
 * @param null $param
 * @return mixed|null
 */
function files($param = null)
{
    if (is_null($param)) return \http()->_files()->_get();

    return \http()->_files()->get($param);
}

/**
 * @param null $param
 * @return mixed|null
 */
function headers($param = null)
{

    if (is_null($param)) return \http()->_header()->_get();

    return \http()->_header()->get($param);
}

/**
 * @return Query
 */
function db(): Query
{
    return Query::getInstance();
}

/**
 * @return Composer
 */
function composer(): Composer
{
    return Composer::getInstance();
}

/**
 * @return Form
 */
function form(): Form {
    return Form::getInstance();
}

/**
 * @return Http
 */
function http(): Http
{
    return Http::getInstance();
}

/**
 * @return bool
 */
function has_get_request(): bool
{
    return array_size(get()) > 0;
}

/**
 * @return bool
 */
function has_post_request(): bool
{
    return array_size(post()) > 0;
}

/**
 * @return Pagination
 */
function pagination(): Pagination {
    return Pagination::getInstance();
}

/**
 * @return bool
 */
function is_logged_in(): bool
{
    return User::isLoggedIn();
}

/**
 * @param string|null $key
 * @return User|mixed|null
 */
function user(string $key = null)
{
    $user = User::getInstance();
    return is_null($key) ? $user : $user->getValue($key, null);
}

/**
 * @param string $url
 * @param array $header
 * @param array $data
 */
function redirect(string $url, array $header = [], array $data = []) {
    $redirect = \http()->redirect();
    $redirect->setUrl($url);
    if (!empty($header)) $redirect->setHeaderArray($header);
    if (!empty($data)) foreach ($data as $key => $value) $redirect->setData($key, $value);
    $redirect->initiate();
}

/**
 * Determines the mimetype of a file by looking at its extension.
 *
 * @param string $filename
 *
 * @return null|string
 */
function mime_type_from_filename(string $filename)
{
    return mime_type_from_extension(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Maps a file extensions to a mimetype.
 *
 * @param string $extension string The file extension.
 *
 * @return string|null
 * @link http://svn.apache.org/repos/asf/httpd/httpd/branches/1.3.x/conf/mime.types
 */
function mime_type_from_extension(string $extension)
{
    static $mime_types = [
        '7z' => 'application/x-7z-compressed',
        'aac' => 'audio/x-aac',
        'ai' => 'application/postscript',
        'aif' => 'audio/x-aiff',
        'asc' => 'text/plain',
        'asf' => 'video/x-ms-asf',
        'atom' => 'application/atom+xml',
        'avi' => 'video/x-msvideo',
        'bmp' => 'image/bmp',
        'bz2' => 'application/x-bzip2',
        'cer' => 'application/pkix-cert',
        'crl' => 'application/pkix-crl',
        'crt' => 'application/x-x509-ca-cert',
        'css' => 'text/css',
        'csv' => 'text/csv',
        'cu' => 'application/cu-seeme',
        'deb' => 'application/x-debian-package',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'dvi' => 'application/x-dvi',
        'eot' => 'application/vnd.ms-fontobject',
        'eps' => 'application/postscript',
        'epub' => 'application/epub+zip',
        'etx' => 'text/x-setext',
        'flac' => 'audio/flac',
        'flv' => 'video/x-flv',
        'gif' => 'image/gif',
        'gz' => 'application/gzip',
        'htm' => 'text/html',
        'html' => 'text/html',
        'ico' => 'image/x-icon',
        'ics' => 'text/calendar',
        'ini' => 'text/plain',
        'iso' => 'application/x-iso9660-image',
        'jar' => 'application/java-archive',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'js' => 'text/javascript',
        'json' => 'application/json',
        'latex' => 'application/x-latex',
        'log' => 'text/plain',
        'm4a' => 'audio/mp4',
        'm4v' => 'video/mp4',
        'mid' => 'audio/midi',
        'midi' => 'audio/midi',
        'mov' => 'video/quicktime',
        'mp3' => 'audio/mpeg',
        'mp4' => 'video/mp4',
        'mp4a' => 'audio/mp4',
        'mp4v' => 'video/mp4',
        'mpe' => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'mpg4' => 'video/mp4',
        'oga' => 'audio/ogg',
        'ogg' => 'audio/ogg',
        'ogv' => 'video/ogg',
        'ogx' => 'application/ogg',
        'pbm' => 'image/x-portable-bitmap',
        'pdf' => 'application/pdf',
        'pgm' => 'image/x-portable-graymap',
        'png' => 'image/png',
        'pnm' => 'image/x-portable-anymap',
        'ppm' => 'image/x-portable-pixmap',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'ps' => 'application/postscript',
        'qt' => 'video/quicktime',
        'rar' => 'application/x-rar-compressed',
        'ras' => 'image/x-cmu-raster',
        'rss' => 'application/rss+xml',
        'rtf' => 'application/rtf',
        'sgm' => 'text/sgml',
        'sgml' => 'text/sgml',
        'svg' => 'image/svg+xml',
        'swf' => 'application/x-shockwave-flash',
        'tar' => 'application/x-tar',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff',
        'torrent' => 'application/x-bittorrent',
        'ttf' => 'application/x-font-ttf',
        'txt' => 'text/plain',
        'wav' => 'audio/x-wav',
        'webm' => 'video/webm',
        'wma' => 'audio/x-ms-wma',
        'wmv' => 'video/x-ms-wmv',
        'woff' => 'application/x-font-woff',
        'wsdl' => 'application/wsdl+xml',
        'xbm' => 'image/x-xbitmap',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xml' => 'application/xml',
        'xpm' => 'image/x-xpixmap',
        'xwd' => 'image/x-xwindowdump',
        'yaml' => 'text/yaml',
        'yml' => 'text/yaml',
        'zip' => 'application/zip',
    ];

    $extension = strtolower($extension);

    return isset($mime_types[$extension])
        ? $mime_types[$extension]
        : null;
}

/**
 * @return string
 */
function server_protocol() {
    return (http()->_server()["HTTPS"] == "on") ? "https://" : "http://";
}

/**
 * @return string
 */
function server_host() {

    return http()->_server()["HTTP_HOST"] ?? 'localhost';
}

/**
 * @return RouteEntry
 */
function current_route()
{
    return Route::getCurrentRoute();
}

/**
 * This function returns a string of the current uri
 * @return string
 */
function current_uri(): string
{
    return (((\http()->_server()['REQUEST_URI_ORIGINAL'] ?: \http()->_server()['REQUEST_URI'])) ?: '');
}

/**
 * This function returns a string of the current url
 * @return string
 */
function current_url(): string
{
    return server_protocol() . server_host() . current_uri();
}

/**
 * This function returns a string of the base url
 *
 * @param string $url
 * @return string
 */
function base_url(string $url = null): string
{
    $host = server_host();

    if (!($isNull = is_null($url)) && preg_match_all('/\{(.*?)\}/', $url, $matches)) {

        $args = array_map(function ($m) {
            return trim($m, '?');
        }, $matches[1]);

        foreach ($args as $arg) {
            $uriArgs = get_uri_args();
            if (isset($uriArgs[$arg])) $url = str_replace('{' . $arg . '}', $uriArgs[$arg], $url);
        }
    }

    if (!$isNull && str_contains($url, $host)) $url = str_start_from($url, $host);

    if (!$isNull) {
        $routeEntry = Route::getRouteEntry($url);
        if ($routeEntry instanceof RouteEntry) {
            if ($routeEntry->isRequireLogIn() === true && !is_logged_in()) return '#';
        }
    }

    $uri = (((\http()->_server()['REQUEST_URI_ORIGINAL'] ?: \http()->_server()['REQUEST_URI'])) ?: '');

    if (str_contains($uri, APP_ROOT_FOLDER)) $host .= ("/" . str_end_at($uri, APP_ROOT_FOLDER));

    return server_protocol() . preg_replace("/\/\//", "/", $isNull ? $host : "{$host}/{$url}");
}

/**
 * This function returns the current uri arguments
 * @param null $arg
 * @param null $default
 * @return mixed|null
 */
function get_uri_args($arg = null, $default = null)
{
    $args = http()->_server()->get("URI_ARGS");
    return !is_null($arg) ? (isset($args[$arg]) ? $args[$arg] : $default) : $args;
}

/**
 * @return mixed|null
 */
function csrf_token() {
    return CSRF::getInstance()->getToken();
}

/**
 * @return mixed|null
 */
function track_token() {
    return Track::generateToken();
}

/**
 * Misc functions ends here
 */