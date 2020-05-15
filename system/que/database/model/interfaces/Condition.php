<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 5/9/2020
 * Time: 1:03 AM
 */

namespace que\database\model\interfaces;


use DateTime;

interface Condition
{
    /**
     * Condition constructor.
     * @param $key
     * @param $value
     */
    public function __construct($key, $value);

    /**
     * @return mixed
     */
    public function getKey();

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @param $variable
     * @return bool
     */
    public function is($variable): bool;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @param $variable
     * @return bool
     */
    public function isIdentical($variable): bool;

    /**
     * @param $variable
     * @return bool
     */
    public function isNotIdentical($variable): bool;

    /**
     * @param array $variable
     * @return bool
     */
    public function isIdenticalToAny(array $variable): bool;

    /**
     * @param array $variable
     * @return bool
     */
    public function isNotIdenticalToAny(array $variable): bool;

    /**
     * @param $variable
     * @return bool
     */
    public function isEqual($variable): bool;

    /**
     * @param $variable
     * @return bool
     */
    public function isNotEqual($variable): bool;

    /**
     * @param array $variable
     * @return bool
     */
    public function isEqualToAny(array $variable): bool;

    /**
     * @param array $variable
     * @return bool
     */
    public function isNotEqualToAny(array $variable): bool;

    /**
     * @param $variable
     * @return bool
     */
    public function isNumberGreaterThan($variable): bool;

    /**
     * @param $variable
     * @return bool
     */
    public function isNumberGreaterThanOrEqual($variable): bool;

    /**
     * @param $variable
     * @return bool
     */
    public function isNumberLessThan($variable): bool;

    /**
     * @param $variable
     * @return bool
     */
    public function isNumberLessThanOrEqual($variable): bool;

    /**
     * @return bool
     */
    public function isArray(): bool;

    /**
     * @return bool
     */
    public function isObject(): bool;

    /**
     * @return bool
     */
    public function isString(): bool;

    /**
     * @return bool
     */
    public function isNumeric(): bool;

    /**
     * @return bool
     */
    public function isNumber(): bool;

    /**
     * @return bool
     */
    public function isInteger(): bool;

    /**
     * @return bool
     */
    public function isNumberFormat(): bool;

    /**
     * @return bool
     */
    public function isBlank(): bool;

    /**
     * @return bool
     */
    public function isUrl(): bool;

    /**
     * @return bool
     */
    public function isUsername(): bool;

    /**
     * @return bool
     */
    public function isChar(): bool;

    /**
     * @return bool
     */
    public function isAlphaNumeric(): bool;

    /**
     * @return bool
     */
    public function isEmail(): bool;

    /**
     * @param string $format
     * @return bool
     */
    public function isDate(string $format): bool;

    /**
     * @param string $format
     * @param DateTime $compare
     * @return bool
     */
    public function isDateGraterThan(string $format, DateTime $compare): bool;

    /**
     * @param string $format
     * @param DateTime $compare
     * @return bool
     */
    public function isDateGraterThanOrEqual(string $format, DateTime $compare): bool;

    /**
     * @param string $format
     * @param DateTime $compare
     * @return bool
     */
    public function isDateLessThan(string $format, DateTime $compare): bool;

    /**
     * @param string $format
     * @param DateTime $compare
     * @return bool
     */
    public function isDateLessThanOrEqual(string $format, DateTime $compare): bool;

    /**
     * @param string $regex
     * @return bool
     */
    public function matches(string $regex): bool;

    /**
     * @param $format
     * @return string
     */
    public function getDate($format): string;

    /**
     * @return string
     */
    public function getAge(): string;

    /**
     * @return bool
     */
    public function isBool(): bool;

    /**
     * @return bool
     */
    public function isIp(): bool;

    /**
     * @return bool
     */
    public function isIpv4(): bool;

    /**
     * @return bool
     */
    public function isIpv4NoPriv(): bool;

    /**
     * @return bool
     */
    public function isIpv6(): bool;

    /**
     * @return bool
     */
    public function isIpv6NoPriv(): bool;

    /**
     * @param int $max
     * @return bool
     */
    public function hasMaxWord(int $max): bool;

    /**
     * @param int $min
     * @return bool
     */
    public function hasMinWord(int $min = 1): bool;

    /**
     * @param int $max
     * @return bool
     */
    public function hasMaxLength(int $max): bool;

    /**
     * @param int $min
     * @return bool
     */
    public function hasMinLength(int $min = 1): bool;

    /**
     * @param string $algo
     * @return Condition
     */
    public function hash(string $algo): Condition;

    /**
     * @return Condition
     */
    public function toUpper(): Condition;

    /**
     * @return Condition
     */
    public function toLower(): Condition;

    /**
     * @return Condition
     */
    public function toUcFirst(): Condition;

    /**
     * @return Condition
     */
    public function toUcWords(): Condition;

    /**
     * @return Condition
     */
    public function trim(): Condition;

    /**
     * @param $function
     * @param mixed ...$parameter
     * @return Condition
     */
    public function _call($function, ...$parameter): Condition;

}