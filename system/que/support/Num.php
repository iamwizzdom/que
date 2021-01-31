<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/6/2020
 * Time: 5:42 PM
 */

namespace que\support;


use que\utility\money\Helper;
use que\utility\money\Item;

class Num
{
    /**
     * @param string $number
     * @param int $start
     * @param int $end
     * @return string
     */
    public static function hide_number(string $number, int $start = 0, int $end = 1) {
        return hide_number($number, $start, $end);
    }

    /**
     * @param string $number
     * @return mixed|string
     */
    public static function filter_number (string $number) {
        return filter_number($number);
    }

    /**
     * @param string $phone
     * @param string $prefix
     * @return mixed|string
     */
    public static function format_phone (string $phone, string $prefix) {
        return format_phone($phone, $prefix);
    }

    /**
     * @param int $num
     * @return string
     */
    public static function short (int $num) {
        return number_short($num);
    }

    /**
     * @param int $num
     * @param int $total
     * @param int $decimal
     * @return string
     */
    public static function percent (int $num, int $total, int $decimal = 2) {
        return number_percent($num, $total, $decimal);
    }

    /**
     * @param int $num
     * @return bool|mixed|string|null
     */
    public static function to_word (int $num) {
        return number_to_word($num);
    }

    /**
     * @param int $num
     * @param int $nearest
     * @return float|int
     */
    public static function round (int $num, int $nearest = 100) {
        return number_round($num, $nearest);
    }

    /**
     * @param int $bytes
     * @param int $decimals
     * @return string
     */
    public static function convert_bytes (int $bytes, int $decimals = 0) {
        return convert_bytes($bytes, $decimals);
    }

    /**
     * @param int $mega_bytes
     * @param int $decimals
     * @return float
     */
    public static function convert_mega_bytes (int $mega_bytes, int $decimals = 0) {
        return convert_mega_bytes($mega_bytes, $decimals);
    }

    /**
     * @param $amount
     * @param null $precision
     * @return Item
     */
    public static function item($amount, $precision = null) {
        return new Item($amount, $precision);
    }

    /**
     * @return Helper
     */
    public static function item_helper() {
        return new Helper();
    }
}