<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/10/2018
 * Time: 2:28 PM
 */

namespace que\common\validator\condition;

use DateTime;
use Exception;
use que\common\validator\interfaces\Condition as ConditionAlias;
use que\support\Arr;
use que\utility\client\IP;
use que\utility\hash\Hash;
use que\utility\random\UUID;

class Condition implements ConditionAlias
{

    /**
     * @var mixed
     */
    private $key;

    /**
     * @var mixed
     */
    private $value;

    /**
     * Condition constructor.
     * @param $key
     * @param $value
     */
    public function __construct($key, $value)
    {
        $this->setKey($key);
        $this->setValue($value);
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    private function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    private function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @param $variable
     * @return bool
     */
    public function is($variable): bool {
        return (
            $this->getValue() === $variable ||
            $this->getValue() == $variable
        );
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool {
        return empty($this->getValue()) && $this->getValue() != "0";
    }

    /**
     * @return bool
     */
    public function isNotEmpty(): bool {
        return !$this->isEmpty();
    }

    /**
     * @param $variable
     * @return bool
     */
    public function isIdentical($variable): bool {
        return $this->getValue() === $variable;
    }

    /**
     * @param $variable
     * @return bool
     */
    public function isNotIdentical($variable): bool {
        return $this->getValue() !== $variable;
    }

    /**
     * @inheritDoc
     */
    public function isIdenticalToAny(array $variable): bool
    {
        // TODO: Implement isIdenticalToAny() method.
        return in_array($this->getValue(), $variable, true);
    }

    /**
     * @inheritDoc
     */
    public function isNotIdenticalToAny(array $variable): bool
    {
        // TODO: Implement isNotIdenticalToAny() method.
        return !in_array($this->getValue(), $variable, true);
    }

    /**
     * @param $variable
     * @return bool
     */
    public function isEqual($variable): bool {
        return $this->getValue() == $variable;
    }

    /**
     * @param $variable
     * @return bool
     */
    public function isNotEqual($variable): bool {
        return $this->getValue() != $variable;
    }

    /**
     * @inheritDoc
     */
    public function isEqualToAny(array $variable): bool
    {
        // TODO: Implement isEqualToAny() method.
        return in_array($this->getValue(), $variable);
    }

    /**
     * @inheritDoc
     */
    public function isNotEqualToAny(array $variable): bool
    {
        // TODO: Implement isNotEqualToAny() method.
        return !in_array($this->getValue(), $variable);
    }

    /**
     * @param $number
     * @return bool
     */
    public function isNumberGreaterThan(int $number): bool {
        return $this->getValue() > $number;
    }

    /**
     * @param $number
     * @return bool
     */
    public function isNumberGreaterThanOrEqual(int $number): bool {
        return $this->getValue() >= $number;
    }

    /**
     * @param $number
     * @return bool
     */
    public function isNumberLessThan(int $number): bool {
        return $this->getValue() < $number;
    }

    /**
     * @param $number
     * @return bool
     */
    public function isNumberLessThanOrEqual(int $number): bool {
        return $this->getValue() <= $number;
    }

    /**
     * @inheritDoc
     */
    public function isFloatingNumber(): bool
    {
        // TODO: Implement isFloatingNumber() method.
        return filter_var($this->getValue(), FILTER_VALIDATE_FLOAT) !== false;
    }

    /**
     * @inheritDoc
     */
    public function isFloatingNumberGreaterThan(int $number): bool
    {
        // TODO: Implement isFloatingNumberGreaterThan() method.
        return $this->isFloatingNumber() && ($this->getValue() > $number);
    }

    /**
     * @inheritDoc
     */
    public function isFloatingNumberGreaterThanOrEqual(int $number): bool
    {
        // TODO: Implement isFloatingNumberGreaterThanOrEqual() method.
        return $this->isFloatingNumber() && ($this->getValue() >= $number);
    }

    /**
     * @inheritDoc
     */
    public function isFloatingNumberLessThan(int $number): bool
    {
        // TODO: Implement isFloatingNumberLessThan() method.
        return $this->isFloatingNumber() && ($this->getValue() < $number);
    }

    /**
     * @inheritDoc
     */
    public function isFloatingNumberLessThanOrEqual(int $number): bool
    {
        // TODO: Implement isFloatingNumberLessThanOrEqual() method.
        return $this->isFloatingNumber() && ($this->getValue() <= $number);
    }

    /**
     * @return bool
     */
    public function isArray(): bool
    {
        // TODO: Implement isArray() method.
        return Arr::is_accessible($this->getValue());
    }

    /**
     * @return bool
     */
    public function isObject(): bool
    {
        // TODO: Implement isObject() method.
        return is_object($this->getValue());
    }

    /**
     * @return bool
     */
    public function isNumeric(): bool {
        return is_numeric($this->getValue());
    }

    /**
     * @return bool
     */
    public function isString(): bool {
        return is_string($this->getValue());
    }

    /**
     * @inheritDoc
     */
    public function isPhoneNumber(): bool
    {
        // TODO: Implement isPhoneNumber() method.
        return preg_match("/^(\+)?[0-9]+$/", $this->getValue()) == 1;
    }

    /**
     * @return bool
     */
    public function isNumberFormat(): bool {
        return preg_match("/^[0-9.,]+$/", $this->getValue()) == 1;
    }

    /**
     * @return bool
     */
    public function isNumber(): bool {
        return preg_match("/^[0-9]+$/", $this->getValue()) == 1;
    }

    /**
     * @return bool
     */
    public function isInteger(): bool {
        return preg_match("/^[0-9\-]+$/", $this->getValue()) == 1;
    }

    /**
     * @param string|null $pattern
     * @return bool
     */
    public function isUrl(string $pattern = null): bool {
        $filter = filter_var($this->getValue(), FILTER_VALIDATE_URL);
        if ($pattern !== null) return ($filter !== false && preg_match($pattern, $this->getValue()) == 1);
        return $filter !== false;
    }

    /**
     * @return bool
     */
    public function isUsername(): bool {
        return preg_match("/^[a-zA-Z0-9._]+$/", $this->getValue()) == 1;
    }

    /**
     * @return bool
     */
    public function isChar(): bool {
        return preg_match("/^[a-zA-Z]+$/", $this->getValue()) == 1;
    }

    /**
     * @return bool
     */
    public function isAlphaNumeric(): bool {
        return preg_match("/^(?=.*\d)(?=.*[a-zA-Z])/", $this->getValue()) == 1;
    }

    /**
     * @return bool
     */
    public function isEmail(): bool {
        return filter_var($this->getValue(),FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @return bool
     */
    public function isUUID(): bool {
        return UUID::is_valid($this->getValue());
    }

    /**
     * @inheritDoc
     */
    public function isUniqueInDB($table, $column, $ignoreID = null, string $ignoreColumn = 'id'): bool
    {
        // TODO: Implement isUniqueInDB() method.
        return $this->isNotFoundInDB($table, $column, false, $ignoreID, $ignoreColumn);
    }

    /**
     * @inheritDoc
     */
    public function isFoundInDB($table, $column, bool $considerIsActive = false,
                                $ignoreID = null, string $ignoreColumn = 'id'): bool
    {
        // TODO: Implement isFoundInDB() method.
        $where = [
            $column => $this->getValue()
        ];

        if ($considerIsActive === true) {
            $where[config('database.table_status_key', 'is_active')] = STATE_ACTIVE;
        }

        if (!empty($ignoreID)) {
            $where["{$ignoreColumn}[!=]"] = $ignoreID;
        }

        return db()->check($table, ['AND' => $where])->isSuccessful();
    }

    /**
     * @inheritDoc
     */
    public function isNotFoundInDB($table, $column, bool $considerIsActive = false,
                                   $ignoreID = null, string $ignoreColumn = 'id'): bool
    {
        // TODO: Implement isNotFoundInDB() method.
        return !$this->isFoundInDB($table, $column, $considerIsActive, $ignoreID, $ignoreColumn);
    }

    /**
     * @param string $format
     * @return bool
     */
    public function isDate(string $format): bool {
        return DateTime::createFromFormat($format, $this->getValue()) instanceof DateTime;
    }

    /**
     * @param string $format
     * @param DateTime $compare
     * @return bool
     */
    public function isDateGreaterThan(string $format, DateTime $compare): bool {
        $date = DateTime::createFromFormat($format, $this->getValue());
        if (!$date instanceof DateTime) return false;
        return $date->getTimestamp() > $compare->getTimestamp();
    }

    /**
     * @param string $format
     * @param DateTime $compare
     * @return bool
     */
    public function isDateGreaterThanOrEqual(string $format, DateTime $compare): bool {
        $date = DateTime::createFromFormat($format, $this->getValue());
        if (!$date instanceof DateTime) return false;
        return $date->getTimestamp() >= $compare->getTimestamp();
    }

    /**
     * @param string $format
     * @param DateTime $compare
     * @return bool
     */
    public function isDateLessThan(string $format, DateTime $compare): bool {
        $date = DateTime::createFromFormat($format, $this->getValue());
        if (!$date instanceof DateTime) return false;
        return $date->getTimestamp() < $compare->getTimestamp();
    }

    /**
     * @param string $format
     * @param DateTime $compare
     * @return bool
     */
    public function isDateLessThanOrEqual(string $format, DateTime $compare): bool {
        $date = DateTime::createFromFormat($format, $this->getValue());
        if (!$date instanceof DateTime) return false;
        return $date->getTimestamp() <= $compare->getTimestamp();
    }

    /**
     * @param string $regex
     * @return bool
     */
    public function matches(string $regex): bool
    {
        return preg_match($regex, $this->getValue()) == 1;
    }

    /**
     * @return bool
     */
    public function isBool(): bool {
        return in_array($this->getValue(), [true, false, 0, 1, '0', '1'], true);
    }

    /**
     * @inheritDoc
     */
    public function isTrue(callable $test): bool
    {
        // TODO: Implement isTrue() method.
        return !$this->isFalse($test);
    }

    /**
     * @inheritDoc
     */
    public function isFalse(callable $test): bool
    {
        // TODO: Implement isFalse() method.
        return !$test($this->getValue());
    }

    /**
     * @return bool
     */
    public function isIp(): bool {
        return filter_var($this->getValue(), FILTER_VALIDATE_IP) !== false;
    }

    /**
     * @return bool
     */
    public function isIpv4(): bool {
        return IP::isIpv4($this->getValue());
    }

    /**
     * @return bool
     */
    public function isIpv4NoPriv(): bool {
        return IP::isIpv4NoPriv($this->getValue());
    }

    /**
     * @return bool
     */
    public function isIpv6(): bool {
        return IP::isIpv6($this->getValue());
    }

    /**
     * @return bool
     */
    public function isIpv6NoPriv(): bool {
        return IP::isIpv6NoPriv($this->getValue());
    }

    /**
     * @param int $max
     * @return bool
     */
    public function hasMaxWord(int $max): bool {
        return str_word_count($this->getValue()) <= $max;
    }

    /**
     * @param int $min
     * @return bool
     */
    public function hasMinWord(int $min = 1): bool {
        return str_word_count($this->getValue()) >= $min;
    }

    /**
     * @param int $max
     * @return bool
     */
    public function hasMaxLength(int $max): bool {
        return strlen($this->getValue()) <= $max;
    }

    /**
     * @param int $min
     * @return bool
     */
    public function hasMinLength(int $min = 1): bool {
        return strlen($this->getValue()) >= $min;
    }

    /**
     * @param string $algo
     * @return Condition
     */
    public function hash(string $algo = "SHA256"): ConditionAlias {
        $this->setValue(Hash::sha($this->getValue(), $algo));
        return $this;
    }

    /**
     * @return $this
     */
    public function toUpper(): ConditionAlias {
        $this->setValue(strtoupper($this->getValue()));
        return $this;
    }

    /**
     * @return $this
     */
    public function toLower(): ConditionAlias {
        $this->setValue(strtolower($this->getValue()));
        return $this;
    }

    /**
     * @return $this
     */
    public function toUcFirst(): ConditionAlias {
        $this->setValue(ucfirst($this->getValue()));
        return $this;
    }

    /**
     * @return $this
     */
    public function toUcWords(): ConditionAlias {
        $this->setValue(ucwords($this->getValue()));
        return $this;
    }

    /**
     * @param string $charlist
     * @return $this
     */
    public function trim(string $charlist = " \t\n\r\0\x0B"): ConditionAlias {
        $this->setValue(trim($this->getValue(), $charlist));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toDate(string $format): ConditionAlias
    {
        // TODO: Implement toDateFormat() method.
        $this->setValue(get_date($format, $this->getValue(), $this->getValue()));
        return $this;
    }

    /**
     * @param $function
     * @param mixed ...$parameter
     * @return Condition
     */
    public function _call($function, ...$parameter): ConditionAlias {
        if (!function_exists($function)) return $this;
        array_unshift($parameter, $this->getValue());
        $this->setValue(call_user_func($function, ...$parameter));
        return $this;
    }
}