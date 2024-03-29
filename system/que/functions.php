<?php

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;
use que\common\exception\PreviousException;
use que\common\exception\QueException;
use que\common\exception\QueRuntimeException;
use que\common\exception\RouteException;
use que\common\time\Time;
use que\common\validator\Track;
use que\database\DB;
use que\error\log\Logger;
use que\error\RuntimeError;
use que\http\HTTP;
use que\http\request\Request;
use que\route\Route;
use que\route\RouteEntry;
use que\security\CSRF;
use que\security\interfaces\RoutePermission;
use que\session\Session;
use que\support\Arr;
use que\support\Config;
use que\support\Env;
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
 * String functions starts here
 */

/**
 * @param int $length
 * @param bool $hex
 * @return false|string
 */
function unique_id(int $length = 32, bool $hex = true): bool|string
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
    return $hex ? substr(bin2hex($r), 0, $length) : $r;
}

/**
 * @param int $length
 * @return string
 */
#[Pure] function str_rand(int $length = 6): string
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
 * @param bool $case_insensitive
 * @return bool
 */
#[Pure] function str__contains(string $haystack, string $needle, bool $case_insensitive = false): bool
{
    return $case_insensitive ? stripos($haystack, $needle) !== false : strpos($haystack, $needle) !== false;
}

/**
 * @param string $haystack
 * @param array $needles
 * @param bool $case_insensitive
 * @return bool
 */
#[Pure] function str_contains_any(string $haystack, array $needles, bool $case_insensitive = false): bool
{
    $count = 0;
    foreach ($needles as $needle) {
        if (str__contains($haystack, $needle, $case_insensitive)) {
            $count++;
            break;
        }
    }
    return $count > 0;
}

/**
 * This function will return a substring of
 * @param string $haystack
 * starting from the first occurrence of
 * @param string $needle
 * @param int $extra | This defines a number of extra strings
 * to allow from the the first occurrence of $needle
 * @return bool|string
 */
#[Pure] function str_start_from(string $haystack, string $needle, int $extra = 0): bool|string
{
    if (($pos = strpos($haystack, $needle)) === false) return $haystack;
    return substr($haystack, $pos = ($pos + strlen($needle)), ((strlen($haystack) - $pos) + $extra));
}

/**
 * This function will return a substring of
 * @param string $haystack
 * ending at the first occurrence of
 * @param string $needle
 * @param int $extra - This defines a number of extra strings
 * to subtract from the first occurrence of $needle
 * @return bool|string
 */
#[Pure] function str_end_at(string $haystack, string $needle, int $extra = 0): bool|string
{
    if (($pos = strpos($haystack, $needle)) === false) return $haystack;
    return substr($haystack, 0, (($pos + strlen($needle)) - $extra));
}

/**
 * This function will return the needle's number
 * of occurrence in the haystack
 * @param string $haystack
 * @param string $needle
 * @return int
 */
#[Pure] function str_char_count(string $haystack, string $needle): int
{
    $count = 0; $len = strlen($needle);
    while (($pos = strpos($haystack, $needle)) !== false) {
        $haystack = substr($haystack, ($pos + $len));
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
#[Pure] function str_ellipsis(string $string, int $length = 50, string $ellipsis = null): string
{
    $size = strlen($string);
    $string = substr($string, 0, ($size > $length ? $length : $size));
    $end = strrpos($string, " ");
    $end_pos = ($size > $length ? ($end !== false ? $end : $length) : $size);
    return substr($string, 0, $end_pos) . ($end_pos < $size ? (is_null($ellipsis) ? "..." : $ellipsis) : "");
}

/**
 * This function will replace the first occurrence of $search in $subject
 *
 * @param $search
 * @param $replace
 * @param $subject
 * @return string|string[]
 */
#[Pure] function str_replace_first($search, $replace, $subject): array|string
{

    if ($search == '') return $subject;

    $position = strpos($subject, $search);

    if ($position !== false) {
        return substr_replace($subject, $replace, $position, strlen($search));
    }

    return $subject;
}

/**
 * This function will replace the last occurrence of $search in $subject
 *
 * @param $search
 * @param $replace
 * @param $subject
 * @return string|string[]
 */
#[Pure] function str_replace_last($search, $replace, $subject): array|string
{

    $position = strrpos($subject, $search);

    if ($position !== false) {
        return substr_replace($subject, $replace, $position, strlen($search));
    }

    return $subject;
}

/**
 * This function will remove all occurrences of the needle from the string
 * @param string $string
 * @param string $needle
 * @return string|string[]|null
 */
function str_strip(string $string, string $needle): array|string|null
{
    if (!str__contains($string, $needle)) return $string;
    return preg_replace('/' . $needle .'+/', "", $string);
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
 * Transform two or more spaces into just one space.
 *
 * @param string $string
 * @return string
 */
function str_strip_excess_whitespace(string $string): string
{
    return preg_replace('/\s+?/', " ", $string);
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
 * Remove all repeated occurrence of $char from $subject leaving just one
 *
 * @param string $char
 * @param string $subject
 * @return string
 */
function str_strip_repeated_char(string $char, string $subject): string
{
    return preg_replace('/([' . $char . '])\1+/', '$1', $subject);
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
): array|bool|int|string
{
    foreach ($array as $index => $value) {
        $position = strpos($value, $needle);
        if ($position !== false) {
            switch ($option) {
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
 * @param bool $case_insensitive
 * @return bool
 */
#[Pure] function str__starts_with(string $haystack, string $needle, bool $case_insensitive = false): bool
{
    if ($case_insensitive) {
        $haystack = strtolower($haystack);
        $needle = strtolower($needle);
    }
    return strcmp(substr($haystack, 0, strlen($needle)), $needle) == 0;
}

/**
 * @param string $haystack
 * @param array $needles
 * @param bool $case_insensitive
 * @return bool
 */
#[Pure] function str_starts_with_any(string $haystack, array $needles, bool $case_insensitive = false): bool
{
    foreach ($needles as $needle) {
        if (str__starts_with($haystack, $needle, $case_insensitive)) {
            return true;
        }
    }
    return false;
}

/**
 * @param string $haystack
 * @param string $needle
 * @param bool $case_insensitive
 * @return bool
 */
#[Pure] function str__ends_with(string $haystack, string $needle, bool $case_insensitive = false): bool
{
    if ($case_insensitive) {
        $haystack = strtolower($haystack);
        $needle = strtolower($needle);
    }
    return strcmp(substr($haystack, (strlen($haystack) - ($len = strlen($needle))), $len), $needle) == 0;
}

/**
 *
 * @param string $haystack
 * @param array $needles
 * @param bool $case_insensitive
 * @return bool
 */
#[Pure] function str_ends_with_any(string $haystack, array $needles, bool $case_insensitive = false): bool
{
    foreach ($needles as $needle) {
        if (str__ends_with($haystack, $needle, $case_insensitive)) {
            return true;
        }
    }
    return false;
}

/**
 *
 * Removes line breaks from a string
 *
 * @param string $string
 * @return string
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
 * @return bool|array|string
 */
#[Pure] function str_unique_chars(string $string, bool $returnAsArray = true): bool|array|string
{
    $unique = array_unique(preg_split('/(?<!^)(?!$)/u', $string));
    if (!$returnAsArray) $unique = implode("", $unique);
    return $unique;
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
 * @param string $string
 * @param array $params
 * @return array|string|string[]
 */
function str_interpolate(string $string, array $params) {
    foreach ($params as $key => $value) {
        if ($value === null) $value = 'NULL';
        elseif (is_bool($value)) $value = $value ? 1 : 0;
        elseif (!is_numeric($value)) $value = "'{$value}'";
        $string = str_replace_first($key, "{$value}", $string);
    }
    return $string;
}

/**
 * @param $data
 * @return string
 */
function to_string($data): string
{
    if (is_array($data)) $data = json_encode($data);
    elseif (is_object($data)) {
        if ($data instanceof JsonSerializable) $data = json_encode($data);
        else $data = json_encode((array) $data);
    }
    elseif ($data === true || $data === false) $data = ($data ? 'true' : 'false');
    elseif (is_numeric($data)) $data = "$data";
    elseif (is_null($data)) $data = "null";
    $data = value($data);
    return is_string($data) ? $data : to_string($data);
}

/**
 * Remove all characters from url except domain name and uri
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
 * @return mixed
 */
function filter_email(string $email, int $option = null): mixed
{

    if (!is_email($email)) return false;

    if ($option !== null) {
        $arr = explode("@", $email);
        switch ($option) {
            case FILTER_EMAIL_GET_NAME:
                return $arr[FILTER_EMAIL_GET_NAME];
            case FILTER_EMAIL_GET_HOST:
                return $arr[FILTER_EMAIL_GET_HOST];
            default:
                return $arr;
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
    return DateTime::createFromFormat($format, $date) instanceof DateTime;
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
        log_error($e->getMessage(), $e);
        $dateTime = false;
    }
    return $dateTime ? $dateTime->format($format) : $default;
}

/**
 * This function would retrieve the http bearer token if any
 *
 * @return mixed
 */
function get_bearer_token(): mixed
{
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
 * @param string $number
 * @param int $start
 * @param int $end
 * @return string
 */
#[Pure] function hide_number(string $number, int $start = 0, int $end = 1): string
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
function format_phone(string $phone, string $prefix): string
{
    $phone = filter_number($phone);
    if (str__starts_with($phone, '+')) return $phone;
    elseif (str__starts_with($phone, $prefix))
        return str__starts_with($prefix, '+') ? $phone : "+{$phone}";
    return (str__starts_with($prefix, '+') ? '' : '+') . ($prefix . substr($phone, 1, strlen($phone)));
}


/**
 * @param int $num
 * @return string
 */
#[Pure] function number_short(int $num): string
{
    $k = pow(10, 3);
    $mil = pow(10, 6);
    $bil = pow(10, 9);
    $tril = pow(10, 12);
    $quad = pow(10, 15);
    $quint = pow(10, 18);

    if ($num >= $quint)
        return number_format((int)($num / $quint)) . 'quint';
    elseif ($num >= $quad)
        return number_format((int)($num / $quad)) . 'quad';
    elseif ($num >= $tril)
        return number_format((int)($num / $tril)) . 'tril';
    elseif ($num >= $bil)
        return number_format((int)($num / $bil)) . 'bil';
    elseif ($num >= $mil)
        return number_format((int)($num / $mil)) . 'mil';
    elseif ($num >= $k)
        return number_format((int)($num / $k)) . 'k';
    else return number_format((int)$num);
}

/**
 * @param int $num
 * @param int $total
 * @param int $decimal
 * @return string
 */
#[Pure] function number_percent(int $num, int $total, int $decimal = 2): string
{
    if ($num == 0 || $total == 0) return '0';
    return number_format(($num / $total) * 100, $decimal);
}

/**
 * @param int $num
 * @return bool|string
 */
function number_to_word(int $num): bool|string
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

        if (str__contains($num, '.')) list($num, $fraction) = explode('.', $num);

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
#[Pure] function number_round(int $num, int $nearest = 100): float|int
{
    return ceil($num / $nearest) * $nearest;
}

/**
 * @param int $bytes
 * @param int $decimals
 * @return string
 */
#[Pure] function convert_bytes(int $bytes, int $decimals = 0): string
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
#[Pure] function convert_mega_bytes(int $mega_bytes, int $decimals = 0): float
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
 * @param array $pieces
 * @param string $glue
 * @param callable|null $filter
 * @return string
 */
function serializer(array $pieces, string $glue = '&', callable $filter = null): string
{
    $serial = [];
    foreach ($pieces as $key => $value) {
        if ($filter !== null && !call_user_func($filter, $value, $key)) continue;
        $serial[] = "{$key}={$value}";
    }
    return join($glue, $serial);
}


/**
 * @param array $pieces
 * @param string $glue
 * @param callable|null $filter
 * @return string
 */
function serializer_recursive(array $pieces, string $glue = '&', callable $filter = null): string
{
    $serial = [];
    foreach ($pieces as $key => $value) {
        if (is_array($value)) {
            $pieces = serializer_recursive($value, $glue, $filter);
            if (!empty($pieces)) $serial[] = $pieces;
            continue;
        }
        if ($filter !== null && !call_user_func($filter, $value, $key)) continue;
        $serial[] = "{$key}={$value}";
    }
    return join($glue, $serial);
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
 * @return array
 */
function array_callback(array &$element, $callback, array $affected = []): array
{
    if (!empty($affected)) {
        foreach ($affected as $key) {
            if (array_key_exists($key, $element)) {
                $element[$key] = call_user_func($callback, $element[$key], $key, $element);
            }
        }
        return $element;
    }

    foreach ($element as $key => $value) {
        $element[$key] = call_user_func($callback, $value, $key, $element);
    }

    return $element;
}

/**
 * @param array $element
 * @param $callback
 * @param array $affected
 * @return array
 */
function array_callback_recursive(array &$element, $callback, array $affected = []): array
{
    if (!empty($affected)) {
        foreach ($affected as $key) {
            if (array_key_exists($key, $element)) {
                if (is_array($element[$key])) {
                    $value = &$element[$key];
                    array_callback_recursive($value, $callback, $affected);
                } else $element[$key] = call_user_func($callback, $element[$key], $key, $element);
            }
        }
        return $element;
    }

    foreach ($element as $key => &$value) {
        if (is_array($value)) {
            array_callback_recursive($value, $callback, $affected);
        } else $value = call_user_func($callback, $value, $key, $element);
    }

    return $element;
}

/**
 * @param $element
 * @param $callback
 * @param array $affected
 * @return mixed
 */
function iterable_callback(&$element, $callback, array $affected = []): mixed
{
    if (!is_iterable($element) && !is_object($element)) throw new QueRuntimeException(
        "element passed to iterable_callback is not iterable", "Que Function Error",
        E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance());

    if (array_is_accessible($element)) {

        if (!empty($affected)) {

            foreach ($affected as $key) {
                if (array_key_exists($key, $element)) {
                    $element[$key] = call_user_func($callback, $element[$key], $key, $element);
                }
            }
            return $element;
        }

        foreach ($element as $key => $value) {
            $element[$key] = call_user_func($callback, $value, $key, $element);
        }

        return $element;

    } else {

        if (!empty($affected)) {

            foreach ($affected as $key) {
                if (object_key_exists($key, $element)) {
                    $element->{$key} = call_user_func($callback, $element->{$key}, $key, $element);
                }
            }
            return $element;
        }

        foreach ($element as $key => $value) {
            $element->{$key} = call_user_func($callback, $value, $key, $element);
        }
    }

    return $element;
}

/**
 * @param $element
 * @param $callback
 * @param array $affected
 * @return mixed
 */
function iterable_callback_recursive(&$element, $callback, array $affected = []): mixed
{
    if (!is_iterable($element) && !is_object($element)) throw new QueRuntimeException(
        "element passed to iterable_callback_recursive is not iterable", "Que Function Error",
        E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance());

    if (array_is_accessible($element)) {

        if (!empty($affected)) {
            foreach ($affected as $key)
                if (array_key_exists($key, $element)) {
                    if (is_array($element[$key]) || is_object($element[$key])) {
                        $value = &$element[$key];
                        iterable_callback_recursive($value, $callback, $affected);
                    } else {
                        $element[$key] = call_user_func($callback, $element[$key], $key, $element);
                    }
                }
            return $element;
        }

        foreach ($element as $key => &$value) {
            if (is_array($value)) {
                iterable_callback_recursive($value, $callback, $affected);
            } else {
                $value = call_user_func($callback, $value, $key, $element);
            }
        }

        return $element;

    } else {

        if (!empty($affected)) {
            foreach ($affected as $key)
                if (object_key_exists($key, $element)) {
                    if (is_array($element->{$key}) || is_object($element->{$key})) {
                        $value = &$element->{$key};
                        iterable_callback_recursive($value, $callback, $affected);
                    } else {
                        $element->{$key} = call_user_func($callback, $element->{$key}, $key, $element);
                    }
                }
            return $element;
        }

        foreach ($element as $key => &$value) {
            if (is_array($value) || is_object($value)) {
                iterable_callback_recursive($value, $callback, $affected);
            } else {
                $value = call_user_func($callback, $value, $key, $element);
            }
        }
    }

    return $element;
}

/**
 * @param array $array
 * @param callable $callback
 * @return array
 */
function array_map_recursive(array $array, callable $callback): array
{
    foreach ($array as $key => $value) {
        if (is_array($value)) $array[$key] = array_map_recursive($value, $callback);
        else $array[$key] = $callback($value);
    }
    return $array;
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
 * @param array $array | Array being reduced
 * @param string ...$exclude | Keys to be excluded
 * @return array
 */
function array_exclude(array $array, ...$exclude): array
{
    foreach ($exclude as $key) if (isset($array[$key])) unset($array[$key]);
    return $array;
}

/**
 *
 * @param array $array | Array to extract from
 * @param int $start | Extraction starting point
 * @param int|null $end | Extraction ending point
 * @param bool $unset | Unset extracted values from array
 * @return array
 */
function array_extract(array &$array, int $start, ?int $end = null, bool $unset = false): array
{
    $extracted = [];
    $size = count($array); $keys = array_keys($array);
    for ($i = $start; $i < $size; $i++) {
        $extracted[$keys[$i]] = $array[$keys[$i]];
        if ($unset) unset($array[$keys[$i]]);
        if ($end !== null && $i >= $end) break;
    }
    return $extracted;
}

/**
 * @param array $array | Array to extract from
 * @param array $keys | Key to extracted
 * @param bool $unset | Unset extracted values from array
 * @return array
 */
function array_extract_by_keys(array &$array, array $keys, bool $unset = false): array
{
    $extracted = [];

    foreach ($keys as $key => $value) {

        if (is_array($value)) {

            if (array_key_exists($key, $array) && is_array($array[$key]))
                $extracted[$key] = array_extract_by_keys($array[$key], $value);

        } elseif (array_key_exists($value, $array)) {
            $extracted[$value] = $array[$value];
            if ($unset) unset($array[$value]);
        }

    }
    return $extracted;
}

/**
 * @param array $array
 * @param $from
 * @param $to
 * @return array
 */
function array_rename_key(array &$array, $from, $to): array
{
    if(array_key_exists($from, $array)){
        $keys = array_keys($array);
        $i = 0;
        $index = false;
        foreach($array as $k => $v){
            if($from === $k){
                $index = $i;
                break;
            }
            $i++;
        }
        if ($index !== false) {
            $keys[$i] = $to;
            $array = array_combine($keys, $array);
        }
    }
    return $array;
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
#[Pure] function array_identical(array $array1, array $array2): bool
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
#[Pure] function is_numeric_array(array $array): bool {
    $keys = array_keys($array);
    return array_keys($keys) === $keys;
}

/**
 * @param array $arr
 * @return int
 */
#[Pure] function array_size(array $arr): int
{
    return array_is_accessible($arr) ? count($arr) : 0;
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
 * Specifies number of elements needed
 *
 * @return array This will an array the given value.
 * This is done so that you can multiple a value.
 */
function array_multi($value, int $range): array
{
    $list = [];
    for ($i = 0; $i < $range; $i++)
        $list[] = $value;
    return $list;
}

/**
 * This is a modified alias of the native PHP array_rand function @see array_rand()
 * Only modified to give back random values rather than keys
 *
 * @link https://php.net/manual/en/function.array-rand.php
 *
 * Pick one or more random entries out of an array
 * @param array $input
 * The input array.
 *
 * @param int|null $num_req [optional] <p>
 * Specifies how many entries you want to pick.
 *
 * @return mixed If you do not specify number of entries to pick, array_random will
 * shuffle and return the initial array. However, If you are picking only one entry, array_random
 * returns the value for a random entry. Otherwise, it returns an array
 * of values for the random entries. This is done so that you can pick
 * random a value or values out of an array.
 *
 */
function array_random(array $input, int $num_req = null): mixed
{

    if ($num_req === null) return fisher_yates_shuffle($input);

    $keys = array_keys($input); $size = count($keys);

    if ($num_req == 1) return $input[$keys[mt_rand(0, ($size - 1))]];

    $elements = [];

    for ($i = 0; $i < $num_req; $i++) {
        $elements[] = $input[$keys[mt_rand(0, ($size - 1))]];
    }

    return $elements;
}

/**
 * @param array $haystack
 * @param $needle
 * @param null $default
 * @return mixed
 */
function find_in_array(array $haystack, $needle, $default = null): mixed
{
    return Arr::get($haystack, $needle, $default);
}

/**
 * @param array $input
 * @param callable $callback
 * @return mixed
 */
function array_find(array $input, callable $callback): mixed
{
    foreach ($input as $k => $v) if ($callback($v, $k)) return $v;
    return null;
}

/**
 * Determine whether the given value is array accessible.
 *
 * @param $value
 * @return bool
 */
#[Pure] function array_is_accessible($value): bool {
    return is_array($value) || $value instanceof ArrayAccess;
}

/**
 * Determine if the given key exists in the provided array.
 *
 * @param array $array
 * @param $key
 * @return bool
 */
function array_has_key(array $array, $key): bool {

    if ($array instanceof ArrayAccess) {
        return $array->offsetExists($key);
    }

    return array_key_exists($key, $array);
}

/**
 * Collapse an array of arrays into a single array.
 *
 * @param  array  $array
 * @return array
 */
function array_collapse(array $array): array
{
    $results = [];
    foreach ($array as $key => $values) {
        if (!is_array($values)) continue;
        $results[] = array_exclude($values);
        unset($array[$key]);
    }
    return array_merge($array, ...$results);
}

/**
 * Get an item from an array using "dot" notation.
 *
 * @param array $haystack
 * @param $needle
 * @param null $default
 * @return mixed
 */
function array_get(array $haystack, $needle, $default = null): mixed
{

    if (!array_is_accessible($haystack)) {
        return value($default);
    }

    if (is_null($needle)) {
        return $haystack;
    }

    if (array_has_key($haystack, $needle)) {
        return $haystack[$needle];
    }

    if (!str__contains($needle, '.')) {
        return $haystack[$needle] ?? value($default);
    }

    foreach (explode('.', $needle) as $segment) {
        if (is_object($haystack)) $haystack = (array)$haystack;
        if (array_is_accessible($haystack) && array_has_key($haystack, $segment)) {
            $haystack = $haystack[$segment];
        } else {
            return value($default);
        }
    }

    return $haystack;
}

/**
 * Set an item on an array using dot notation.
 *
 * @param array $array
 * @param $key
 * @param $value
 * @return mixed
 */
function array_set(array &$array, $key, $value): mixed
{

    if (is_null($key)) {
        return $array = $value;
    }

    $keys = explode('.', $key);

    while (count($keys) > 1) {
        $key = array_shift($keys);

        // If the key doesn't exist at this depth, we will just create an empty array
        // to hold the next value, allowing us to create the arrays to hold final
        // values at the correct depth. Then we'll keep digging into the array.
        if (! isset($array[$key]) || ! is_array($array[$key])) {
            $array[$key] = [];
        }

        $array = &$array[$key];
    }

    $array[array_shift($keys)] = $value;

    return $array;
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
function object_to_array(object $object): array
{
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
 * @param $key
 * @param object $object
 * @return bool
 */
function object_key_exists($key, object $object): bool {
    return in_object($key, object_keys($object));
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
#[Pure] function is_numeric_object(object $object): bool {
    return is_numeric_array((array) $object);
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
function object_callback(object $element, $callback, array $affected = [])
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
 * @param mixed ...$exclude | Keys to be excluded
 * @return object
 */
function object_exclude(object $main, ...$exclude): object
{
    foreach ($exclude as $key) unset($main->{$key});
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
#[Pure] function object_extract_by_keys(object $object, array $keys): object
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
 * Get an item from an object using "dot" notation.
 *
 * @param object $haystack
 * @param $needle
 * @param null $default
 * @return mixed
 */
function object_get(object $haystack, $needle, $default = null): mixed
{
    if (is_null($needle)) {
        return $haystack;
    }

    if (object_key_exists($needle, $haystack)) {
        return $haystack->{$needle};
    }

    if (!str__contains($needle, '.')) {
        return $haystack->{$needle} ?? value($default);
    }

    foreach (explode('.', $needle) as $segment) {
        if (is_array($haystack)) $haystack = (object)$haystack;
        if (is_object($haystack) && object_key_exists($segment, $haystack)) {
            $haystack = $haystack->{$segment};
        } else {
            return value($default);
        }
    }

    return $haystack;
}

/**
 * Set an item on an object using dot notation.
 *
 * @param object $object
 * @param $key
 * @param $value
 * @return mixed
 */
function object_set(object &$object, $key, $value): mixed
{

    if (is_null($key)) {
        return $object = $value;
    }

    $keys = explode('.', $key);

    while (count($keys) > 1) {
        $key = array_shift($keys);

        // If the key doesn't exist at this depth, we will just create an empty array
        // to hold the next value, allowing us to create the arrays to hold final
        // values at the correct depth. Then we'll keep digging into the array.
        if (! isset($object->{$key}) || ! is_object($object->{$key})) {
            $object->{$key} = (object)[];
        }

        $object = &$object->{$key};
    }

    $object->{array_shift($keys)} = $value;

    return $object;
}

/**
 * Object functions ends here
 */






/**
 * Misc functions starts here
 */




/**
 * debug_print is used to output all data types
 * @param mixed ...$params
 * @return string|null|void
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
    $print .= "</pre>\n\n";

    if ($end === true) return $print;
    else echo $print;
}

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
#[Pure] function is_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Evaluates if a string is a valid JSON string
 *
 * @param string $string
 * @return bool
 */
function is_json(string $string): bool
{
    return (!(empty($string) && $string != "0") && is_string($string) &&
        ($value = json_decode($string)) != null &&
        json_last_error() == JSON_ERROR_NONE && is_object($value));
}

/**
 * @return Converter
 */
function converter(): Converter
{
    return Converter::getInstance();
}

/**
 * @return Time
 */
function _time(): Time
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
 * @param null $default
 * @return mixed
 */
function post($param = null, $default = null): mixed
{

    if (is_null($param)) return \http()->_post()->_get();

    return \http()->_post()->get($param, $default);
}

/**
 * @param null $param
 * @param null $default
 * @return mixed
 */
function get($param = null, $default = null): mixed
{

    if (is_null($param)) return \http()->_get()->_get();

    return \http()->_get()->get($param, $default);
}

/**
 * @param null $param
 * @param null $default
 * @return mixed
 */
function server($param = null, $default = null): mixed
{

    if (is_null($param)) return \http()->_server()->_get();

    return \http()->_server()->get($param, $default);
}

/**
 * @param null $param
 * @param null $default
 * @return mixed
 */
function request($param = null, $default = null): mixed
{

    if (is_null($param)) return \http()->_request()->_get();

    return \http()->_request()->get($param, $default);
}

/**
 * @param null $param
 * @param null $default
 * @return mixed
 */
function files($param = null, $default = null): mixed
{
    if (is_null($param)) return \http()->_files()->_get();

    return \http()->_files()->get($param, $default);
}

/**
 * @param null $param
 * @param null $default
 * @return mixed
 */
function headers($param = null, $default = null): mixed
{

    if (is_null($param)) return \http()->_header()->_get();

    return \http()->_header()->get($param, $default);
}

/**
 * @param null $param
 * @param null $default
 * @return mixed
 */
function input($param = null, $default = null): mixed
{

    if (is_null($param)) return \http()->input()->_get();

    return \http()->input()->get($param, $default);
}

/**
 * @return Session
 */
function session(): Session {
    return Session::getInstance();
}

/**
 * Return the default value of the given value.
 *
 * @param  mixed  $value
 * @return mixed
 */
function value(mixed $value): mixed
{
    return $value instanceof Closure ? $value() : $value;
}

/**
 * @param array $data
 * @return bool
 */
#[Pure] function is_array_of_arrays(array $data): bool {
    foreach ($data as $value) if (!is_array($value)) return false;
    return true;
}

/**
 * @param array $data
 * @return bool
 */
#[Pure] function is_array_of_objects(array $data): bool {
    foreach ($data as $value) if (!is_object($value)) return false;
    return true;
}

/**
 * Determine if the given value is "blank".
 *
 * @param  mixed  $value
 * @return bool
 */
#[Pure] function is_blank(mixed $value): bool
{
    if (is_null($value)) return true;

    if (is_string($value)) return trim($value) === '';

    if (is_numeric($value) || is_bool($value)) return false;

    if ($value instanceof Countable) return count($value) === 0;

    return empty($value);
}

/**
 * Determine if a value is "filled".
 *
 * @param  mixed  $value
 * @return bool
 */
#[Pure] function is_filled(mixed $value): bool
{
    return !is_blank($value);
}

/**
 * Retry an operation a given number of times.
 *
 * @param callable $callback
 * @param int $times
 * @param float $interval | retrial interval in milliseconds
 * @param callable|null $when
 * @return mixed
 * @throws Exception
 */
function retry(callable $callback, int $times, float $interval = 0, callable $when = null): mixed
{
    $attempts = 0;
    beginning:
    $attempts++;
    $times--;

    try {

        $data = $callback($attempts);
        if ($when && $when($data)) return $data;
        if ($times < 1) return $data;

    } catch (Exception $e) {

        if ($times < 1) throw $e;
    }

    if ($interval) usleep($interval * 1000);

    goto beginning;
}

/**
 * Throw the given exception if the given condition is true.
 *
 * @param mixed $condition
 * @param Throwable|string $exception
 * @param mixed ...$parameters
 * @return mixed
 * @throws Throwable
 */
function throw_if(mixed $condition, Throwable|string $exception, ...$parameters): mixed
{
    if ($condition) {
        throw (is_string($exception) ? new $exception(...$parameters) : $exception);
    }

    return null;
}

/**
 * Throw the given exception unless the given condition is true.
 *
 * @param mixed $condition
 * @param Throwable|string $exception
 * @param array ...$parameters
 * @return mixed
 * @throws Throwable
 */
function throw_unless(mixed $condition, Throwable|string $exception, ...$parameters): mixed
{
    if (!$condition) {
        throw (is_string($exception) ? new $exception(...$parameters) : $exception);
    }

    return $condition;
}

/**
 * Get an item from an array or object using "dot" notation.
 *
 * @param mixed $target
 * @param string|array|int $key
 * @param mixed $default
 * @return mixed
 */
function data_get(mixed $target, int|array|string $key, $default = null): mixed
{
    if (is_null($key)) {
        return $target;
    }

    $key = is_array($key) ? $key : explode('.', $key);

    while (! is_null($segment = array_shift($key))) {

        if ($segment === '*') {

            if (!is_array($target)) {
                return value($default);
            }

            $result = [];

            foreach ($target as $item) {
                $result[] = data_get($item, $key);
            }

            return in_array('*', $key) ? array_collapse($result) : $result;
        }

        if (array_is_accessible($target) && array_has_key($target, $segment)) {
            $target = $target[$segment];
        } elseif (is_object($target) && object_key_exists($segment, $target)) {
            $target = $target->{$segment};
        } else {
            return value($default);
        }
    }

    return $target;
}

/**
 * Set an item on an array or object using dot notation.
 *
 * @param mixed $target
 * @param string|array $key
 * @param mixed $value
 * @param bool $overwrite
 * @return mixed
 */
function data_set(mixed &$target, array|string $key, mixed $value, $overwrite = true): mixed
{
    $segments = is_array($key) ? $key : explode('.', $key);

    if (($segment = array_shift($segments)) === '*') {

        if (!array_is_accessible($target)) $target = [];

        if ($segments) {
            foreach ($target as &$inner) {
                data_set($inner, $segments, $value, $overwrite);
            }
        } elseif ($overwrite) {
            foreach ($target as &$inner) {
                $inner = $value;
            }
        }
    } elseif (array_is_accessible($target)) {
        if ($segments) {
            if (!array_has_key($target, $segment)) {
                $target[$segment] = [];
            }

            data_set($target[$segment], $segments, $value, $overwrite);
        } elseif ($overwrite || !array_has_key($target, $segment)) {
            $target[$segment] = $value;
        }
    } elseif (is_object($target)) {
        if ($segments) {
            if (! isset($target->{$segment})) {
                $target->{$segment} = [];
            }

            data_set($target->{$segment}, $segments, $value, $overwrite);
        } elseif ($overwrite || ! isset($target->{$segment})) {
            $target->{$segment} = $value;
        }
    } else {
        $target = [];

        if ($segments) {
            data_set($target[$segment], $segments, $value, $overwrite);
        } elseif ($overwrite) {
            $target[$segment] = $value;
        }
    }

    return $target;
}

/**
 * @param string $offset
 * @param null $default
 * @return mixed
 */
function config(string $offset, $default = null): mixed
{
    return Config::get($offset, $default);
}

/**
 * @param string $offset
 * @param null $default
 * @return mixed
 */
function env(string $offset, $default = null): mixed
{
    return Env::get($offset, $default);
}

/**
 * @return DB
 */
function db(): DB
{
    return DB::getInstance();
}

/**
 * @param string $modelKey | model key in database config
 * @return mixed
 */
function model(string $modelKey): mixed
{
    $modelKey = Arr::get(config("database.models", []), $modelKey);
    if ($modelKey === null) return null;
    if (!class_exists($modelKey, true)) return null;
    return $modelKey;
}

/**
 * @param bool $singleton
 * @return Composer
 */
function composer(bool $singleton = true): Composer
{
    return Composer::getInstance($singleton);
}

/**
 * @return Form
 */
function form(): Form {
    return Form::getInstance();
}

/**
 * @return HTTP
 */
function http(): HTTP
{
    return HTTP::getInstance();
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
 * @param RouteEntry $entry
 * @return bool
 */
function has_route_permission(RouteEntry $entry): bool {
    $module = $entry->getModule();
    if (!class_exists($module)) return false;
    $module = new $module();
    if (!$module instanceof RoutePermission) return true;
    return $module->hasPermission($entry);
}

/**
 * @param string|null $key
 * @return User|mixed
 */
function user(string $key = null): mixed
{
    $user = User::getInstance();
    return is_null($key) ? $user : $user->getValue($key);
}

/**
 * @param string $route_name
 * @param array $route_args
 * @param array $header
 * @param array $data
 */
function redirect_with_name(string $route_name, array $route_args = [],
                            array $header = [], array $data = []) {
    $redirect = \http()->redirect();
    $redirect->setRouteName($route_name, $route_args);
    if (!empty($header)) $redirect->setHeaderArray($header);
    if (!empty($data)) foreach ($data as $key => $value) $redirect->setData($key, $value);
    $redirect->initiate();
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
 * returns a list of defined constants in class if any
 *
 * @param string|object $class
 * @param null $start_with
 * @return array
 */
function get_class_consts(object|string $class, $start_with = null): array
{
    try {
        $consts = (new ReflectionClass($class))->getConstants();
        return $start_with ? array_filter($consts, function ($key) use ($start_with) {
            return is_array($start_with) ? str_starts_with_any($key, $start_with) : str__starts_with($key, $start_with);
        }, ARRAY_FILTER_USE_KEY) : $consts;
    } catch (Exception $exception) {
        return [];
    }
}

/**
 * Determines the mimetype of a file by looking at its extension or the file itself.
 *
 * @param string $filepath
 * @return null|string
 */
function mime_type_from_filepath(string $filepath): ?string
{
    if ($mime_type = mime_type_from_extension(pathinfo($filepath, PATHINFO_EXTENSION)))
        return $mime_type;

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $finfo_file = finfo_file($finfo, $filepath);
    finfo_close($finfo);
    return $finfo_file;
}

/**
 * Determines the mimetype of a file by looking at its extension.
 *
 * @param string $filename
 *
 * @return null|string
 */
function mime_type_from_filename(string $filename): ?string
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
function mime_type_from_extension(string $extension): ?string
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
    return $mime_types[$extension] ?? null;
}

/**
 * Maps a file mime type to an extension.
 *
 * @param string $mime_type string The file mime type.
 *
 * @return string|null
 */
function extension_from_mime_type(string $mime_type): ?string
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
    $extensions = array_flip($mime_types);

    $mime_type = strtolower($mime_type);

    return $extensions[$mime_type] ?? null;
}

/**
 * @param string $path
 * @return string|string[]
 */
#[Pure] function extension_from_filepath(string $path): array|string
{
    return pathinfo($path, PATHINFO_EXTENSION);
}

/**
 * @param $dir
 * @return bool
 * @throws QueException
 */
function mk_dir($dir): bool
{

    if (!is_dir($dir)) {
        if (!mkdir($dir, 0777, true))
            throw new QueException("Directory could not be created");
    }

    if (!is_dir($dir) || !is_writable($dir))
        throw new QueException("Directory not writable");

    return true;
}

/**
 * Output a gz-file
 * @link https://php.net/manual/en/function.readgzfile.php
 *
 * @param $filepath
 * The file path. This is the file to be opened from the filesystem and its
 * contents written to standard output.
 *
 * @param $filename
 * Defines the output filename
 *
 * @param $auto_download
 * Specifies whether to download file automatically
 *
 * @return bool
 */
function render_file($filepath, $filename = 'download', bool $auto_download = false): bool
{
    http()->_header()->setBulk([
        'Content-Description' => 'Que File Transfer',
        'Content-Disposition' => (($auto_download ? "attachment; " : '') .
            "filename={$filename}." . pathinfo($filepath, PATHINFO_EXTENSION)),
        'Content-Transfer-Encoding' => 'binary',
        'Content-type' => mime_type_from_filepath($filepath),
        'Content-Length' => filesize($filepath),
        'Expires' => 'Fri, 30 Dec 2050 00:00:00 GMT',
        'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        'Pragma' => 'public'
    ]);
    ob_clean();
    $limit = 0;
    while (($status = readgzfile($filepath)) === false && $limit < MAX_RETRY) $limit++;
    return $status !== false;
}

/**
 * @return string
 */
function server_protocol(): string
{
    return (Request::getScheme() . "://");
}

/**
 * @return string
 */
function server_host(): string
{

    return Request::getHttpHost();
}

/**
 * @return RouteEntry
 */
function current_route(): RouteEntry
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
 * This function would return the base url of a route
 * based on its name and args
 *
 * @param string $name
 * @param array $args
 * @return string
 * @throws RouteException
 */
function route_uri(string $name, array $args = []): string
{
    try {
        return \route($name, $args, false);
    } catch (Exception $e) {
        throw new RouteException($e->getMessage(), method_exists($e, 'getTitle') ? $e->getTitle() : 'Route Error',
            HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));
    }
}

/**
 * This function would return the base url of a route
 * based on its name and args
 *
 * @param string $name
 * @param array $args
 * @param bool $addBaseUrl
 * @return string
 * @throws RouteException
 */
function route(string $name, array $args = [], bool $addBaseUrl = true): string
{
    try {
        return Route::getRouteUrl($name, $args, $addBaseUrl);
    } catch (Exception $e) {
        throw new RouteException($e->getMessage(), method_exists($e, 'getTitle') ? $e->getTitle() : 'Route Error',
            HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));
    }
}

/**
 * This function returns a string of the base url
 *
 * @param string|null $url
 *
 * @param bool $forceUrl - This param when true will force base url to return a
 * valid url regardless of login or permission restriction
 *
 * @return string
 */
function base_url(string $url = null, bool $forceUrl = false): string
{

    if (!($isNull = is_null($url)) && (str__starts_with($url, 'http://') ||
            str__starts_with($url, 'https://'))) {
        if (!str__starts_with($url, $base = base_url())) return $url;
        else $url = str_start_from($url, $base);
    }

    $host = server_host();

    if (!$isNull && preg_match_all('/{(.*?)}/', $url, $matches)) {

        $args = array_map(function ($m) {
            return trim($m, '?');
        }, $matches[1]);

        $uriArgs = get_uri_args();
        foreach ($args as $arg) {
            if (isset($uriArgs[$arg])) $url = str_replace('{' . $arg . '}', $uriArgs[$arg], $url);
        }
    }

    if (!$isNull && str__contains($url, $host)) $url = str_start_from($url, $host);

    if (!$isNull) {
        try {
            $routeEntry = Route::getRouteEntryFromUri($url);
            if ($routeEntry instanceof RouteEntry) {
                if ($routeEntry->isRequireLogin() === true && !is_logged_in()) {
                    if (!$forceUrl) return '#';
                } elseif (!has_route_permission($routeEntry) && !$forceUrl) return '#';
            }
        } catch (RouteException $e) {
            throw new QueRuntimeException($e->getMessage(), "Base URL Error", E_ERROR,
                HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));
        }
    }

    $uri = (((\http()->_server()['REQUEST_URI_ORIGINAL'] ?: \http()->_server()['REQUEST_URI'])) ?: '');

    if (!empty(APP_ROOT_FOLDER) && str__contains($uri, APP_ROOT_FOLDER) &&
        in_array(APP_ROOT_FOLDER, $uriTokens = str_tokenize($uri, "/"))) {

        $uri_extract = array_extract($uriTokens, 0, strpos_in_array($uriTokens,
            APP_ROOT_FOLDER, STRPOS_IN_ARRAY_OPT_ARRAY_INDEX));

        $host .= ("/" . implode("/", $uri_extract));
    }

    $url = $isNull ? $host : "{$host}/{$url}";
    $url = str_strip_repeated_char('\/', $url);

    return server_protocol() . str_strip_repeated_char('\\\\', $url);
}

/**
 * @param string|null $uri
 * @param bool $forceUrl
 * @return string
 */
function asset(string $uri = null, bool $forceUrl = false): string
{
    $uri = ltrim($uri, '/');
    $uri = ltrim($uri, '\\');
    return base_url("template/asset/$uri", $forceUrl);
}

/**
 * This function returns the current uri arguments
 * @param null $arg
 * @param null $default
 * @return mixed
 */
function get_uri_args($arg = null, $default = null): mixed
{
    $args = http()->_server()->get("route.params");
    return !is_null($arg) ? (isset($args[$arg]) ? $args[$arg] : $default) : $args;
}

/**
 * @return mixed
 */
function csrf_token(): mixed
{
    return CSRF::getInstance()->getToken();
}

/**
 * @return string
 */
function track_token(): string
{
    return Track::generateToken();
}

/**
 * @param string|mixed ...$message
 * @return array
 */
function get_log_msg_trace(string|array|Exception ...$message): array
{
    if (isset($message[1]) && is_string($message[1])) {
        $message[0] = "$message[0]: " . $message[1];
    }

    $exception = $message[2] ?? ($message[1] ?? null);
    if ($exception instanceof Exception) {
        if ($message[1] instanceof Exception) {
            $message[0] = "$message[0]: " . $exception->getMessage();
        }
        $backtrace = [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTrace()
        ];
    } else {
        $trace = debug_backtrace();
        $backtrace = [
            'file' => $trace[1]['file'],
            'line' => $trace[1]['line'],
            'trace' => array_exclude($trace, 0, 1)
        ];
    }
    return [$message[0], $backtrace];
}

/**
 * An alias of logger
 * @param string ...$message
 * @return bool|int
 * @see logger()
 */
function log_error(string|array|Exception ...$message): bool|int
{
    list($message, $backtrace) = get_log_msg_trace(...$message);
    return logger('error', $message, $backtrace['file'],
        $backtrace['line'], $backtrace['trace'], E_USER_ERROR);
}

/**
 * An alias of logger
 * @param string ...$message
 * @return bool|int
 * @see logger()
 */
function log_warning(string|array|Exception ...$message): bool|int
{
    list($message, $backtrace) = get_log_msg_trace(...$message);
    return logger('warning', $message, $backtrace['file'],
        $backtrace['line'], $backtrace['trace'], E_USER_WARNING);
}

/**
 * An alias of logger
 * @param string ...$message
 * @return bool|int
 * @see logger()
 */
function log_info(string|array|Exception ...$message): bool|int
{
    list($message, $backtrace) = get_log_msg_trace(...$message);
    return logger('info', $message, $backtrace['file'],
        $backtrace['line'], $backtrace['trace'], E_USER_NOTICE);
}

/**
 * An alias of logger
 * @param string ...$message
 * @return bool|int
 * @see logger()
 */
function log_debug(string|array|Exception ...$message): bool|int
{
    list($message, $backtrace) = get_log_msg_trace(...$message);
    return logger('debug', $message, $backtrace['file'],
        $backtrace['line'], $backtrace['trace'], E_USER_NOTICE);
}

/**
 * An alias of logger
 * @param string ...$message
 * @return bool|int
 * @see logger()
 */
function log_default(string|array|Exception ...$message): bool|int
{
    list($message, $backtrace) = get_log_msg_trace(...$message);
    return logger('default', $message, $backtrace['file'],
        $backtrace['line'], $backtrace['trace'], E_USER_NOTICE);
}

/**
 * @param string $type
 * @param string $message
 * @param string $file
 * @param int $line
 * @param array $trace
 * @param int $level
 * @param string|null $destination - directory to store logs
 * @return bool|int
 */
function logger(#[ExpectedValues(['error', 'warning', 'info', 'debug', 'default'])] string $type,
                string $message, string $file, int $line,
                array $trace, int $level, string $destination = null): bool|int
{
    $logger = match ($type) {
        'error' => Logger::error(),
        'warning' => Logger::warning(),
        'info' => Logger::info(),
        'debug' => Logger::debug(),
        default => Logger::default()
    };

    return $logger
        ->setMessage($message)
        ->setFile($file)
        ->setLine($line)
        ->setLevel($level)
        ->setTrace($trace)
        ->setDestination($destination)
        ->log();
}

/**
 * Misc functions ends here
 */
