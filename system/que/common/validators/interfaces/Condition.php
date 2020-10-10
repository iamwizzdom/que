<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 5/9/2020
 * Time: 1:03 AM
 */

namespace que\common\validator\interfaces;


use Closure;
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
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @return bool
     */
    public function isNotEmpty(): bool;

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
     * @param $number
     * @return bool
     */
    public function isNumberGreaterThan(int $number): bool;

    /**
     * @param $number
     * @return bool
     */
    public function isNumberGreaterThanOrEqual(int $number): bool;

    /**
     * @param $number
     * @return bool
     */
    public function isNumberLessThan(int $number): bool;

    /**
     * @param $number
     * @return bool
     */
    public function isNumberLessThanOrEqual(int $number): bool;

    /**
     * @return bool
     */
    public function isFloatingNumber(): bool;

    /**
     * @param $number
     * @return bool
     */
    public function isFloatingNumberGreaterThan(int $number): bool;

    /**
     * @param $number
     * @return bool
     */
    public function isFloatingNumberGreaterThanOrEqual(int $number): bool;

    /**
     * @param $number
     * @return bool
     */
    public function isFloatingNumberLessThan(int $number): bool;

    /**
     * @param $number
     * @return bool
     */
    public function isFloatingNumberLessThanOrEqual(int $number): bool;

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
     * @param string|null $pattern
     * @return bool
     */
    public function isUrl(string $pattern = null): bool;

    /**
     * @return bool
     */
    public function isPhoneNumber(): bool;

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
     * @return bool
     */
    public function isUUID(): bool;

    /**
     * @param $table
     * @param $column
     * @param null $ignoreID
     * @param string $ignoreColumn
     * @return bool
     */
    public function isUniqueInDB($table, $column, $ignoreID = null, string $ignoreColumn = 'id'): bool;

    /**
     * @param $table
     * @param $column
     * @param Closure|null $extraQuery
     * @param null $ignoreID
     * @param string $ignoreColumn
     * @return bool
     */
    public function isFoundInDB($table, $column, ?Closure $extraQuery = null,
                                $ignoreID = null, string $ignoreColumn = 'id'): bool;

    /**
     * @param $table
     * @param $column
     * @param Closure $extraQuery
     * @param null $ignoreID
     * @param string $ignoreColumn
     * @return bool
     */
    public function isNotFoundInDB($table, $column, ?Closure $extraQuery = null,
                                   $ignoreID = null, string $ignoreColumn = 'id'): bool;

    /**
     * @param string|null $format
     * @return bool
     */
    public function isDate(?string $format = null): bool;

    /**
     * @param DateTime $compare
     * @param string|null $format
     * @return bool
     */
    public function isDateGreaterThan(DateTime $compare, ?string $format = null): bool;

    /**
     * @param DateTime $compare
     * @param string|null $format
     * @return bool
     */
    public function isDateGreaterThanOrEqual(DateTime $compare, ?string $format = null): bool;

    /**
     * @param DateTime $compare
     * @param string|null $format
     * @return bool
     */
    public function isDateLessThan(DateTime $compare, ?string $format = null): bool;

    /**
     * @param DateTime $compare
     * @param string|null $format
     * @return bool
     */
    public function isDateLessThanOrEqual(DateTime $compare, ?string $format = null): bool;

    /**
     * @param string $regex
     * @return bool
     */
    public function matches(string $regex): bool;

    /**
     * @return bool
     */
    public function isBool(): bool;

    /**
     * @param callable $test
     * @return bool
     */
    public function isTrue(callable $test): bool;

    /**
     * @param callable $test
     * @return bool
     */
    public function isFalse(callable $test): bool;

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
    public function hash(string $algo = "SHA256"): Condition;

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
     * @param string $charlist
     * @return Condition
     */
    public function trim(string $charlist = " \t\n\r\0\x0B"): Condition;

    /**
     * @param string $format
     * @return Condition
     */
    public function toDate(string $format): Condition;

    /**
     * @param $function
     * @param mixed ...$parameter
     * @note Due to the fact that the subject parameter position might vary across functions,
     * provision has been made for you to define the subject parameter with the key ":subject".
     * e.g to run a function like explode, you are to invoke it as follows: _call('explode', 'delimiter', ':subject');
     * @return Condition
     */
    public function _call($function, ...$parameter): Condition;

}