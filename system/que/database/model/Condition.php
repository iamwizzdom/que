<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/10/2018
 * Time: 2:28 PM
 */

namespace que\database\model;

use DateTime;
use Exception;
use que\database\model\interfaces\Condition as ConditionAlias;
use que\support\Arr;
use que\utility\client\IP;

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
     * @return string
     */
    public function getType(): string {
        return gettype($this->getValue());
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool {
        return empty($this->getValue());
    }

    /**
     * @param $variable
     * @return bool
     */
    public function isIdentical($variable): bool {
        if (is_array($variable)) {
            foreach ($variable as $value)
                if ($value !== $this->getValue()) return false;
            return true;
        }
        return $variable === $this->getValue();
    }

    /**
     * @param $variable
     * @return bool
     */
    public function isNotIdentical($variable): bool {
        if (is_array($variable)) {
            foreach ($variable as $value)
                if ($value !== $this->getValue()) return true;
            return false;
        }
        return $variable !== $this->getValue();
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
        if (is_array($variable)) {
            foreach ($variable as $value)
                if ($value != $this->getValue()) return false;
            return true;
        }
        return $variable == $this->getValue();
    }

    /**
     * @param $variable
     * @return bool
     */
    public function isNotEqual($variable): bool {
        if (is_array($variable)) {
            foreach ($variable as $value)
                if ($value != $this->getValue()) return true;
            return false;
        }
        return $variable != $this->getValue();
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
     * @param $variable
     * @return bool
     */
    public function isNumberGreaterThan($variable): bool {
        if (is_array($variable))
            foreach ($variable as $value)
                if ($value > $this->getValue()) return true;
        return $variable > $this->getValue();
    }

    /**
     * @param $variable
     * @return bool
     */
    public function isNumberGreaterThanOrEqual($variable): bool {
        if (is_array($variable))
            foreach ($variable as $value)
                if ($value >= $this->getValue()) return true;
        return $variable >= $this->getValue();
    }

    /**
     * @param $variable
     * @return bool
     */
    public function isNumberLessThan($variable): bool {
        if (is_array($variable))
            foreach ($variable as $value)
                if ($value < $this->getValue()) return true;
        return $variable < $this->getValue();
    }

    /**
     * @param $variable
     * @return bool
     */
    public function isNumberLessThanOrEqual($variable): bool {
        if (is_array($variable))
            foreach ($variable as $value)
                if ($value <= $this->getValue()) return true;
        return $variable <= $this->getValue();
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
     * @return bool
     */
    public function isBlank(): bool {
        return empty($this->getValue()) && $this->getValue() != "0";
    }

    /**
     * @return bool
     */
    public function isUrl(): bool {
        if (!filter_var($this->getValue(), FILTER_VALIDATE_URL)) return false;
        return true;
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
        return preg_match("/^[a-zA-Z0-9]+$/", $this->getValue()) == 1;
    }

    /**
     * @return bool
     */
    public function isEmail(): bool {
        return filter_var($this->getValue(),FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @param string $format
     * @return bool
     */
    public function isDate(string $format): bool {
        return !DateTime::createFromFormat($format, $this->getValue());
    }

    /**
     * @param string $format
     * @param DateTime $compare
     * @return bool
     */
    public function isDateGraterThan(string $format, DateTime $compare): bool {
        $date = DateTime::createFromFormat($format, $this->getValue());
        if (!$date) return false;
        return $date > $compare;
    }

    /**
     * @param string $format
     * @param DateTime $compare
     * @return bool
     */
    public function isDateGraterThanOrEqual(string $format, DateTime $compare): bool {
        $date = DateTime::createFromFormat($format, $this->getValue());
        if (!$date) return false;
        return $date >= $compare;
    }

    /**
     * @param string $format
     * @param DateTime $compare
     * @return bool
     */
    public function isDateLessThan(string $format, DateTime $compare): bool {
        $date = DateTime::createFromFormat($format, $this->getValue());
        if (!$date) return false;
        return $date < $compare;
    }

    /**
     * @param string $format
     * @param DateTime $compare
     * @return bool
     */
    public function isDateLessThanOrEqual(string $format, DateTime $compare): bool {
        $date = DateTime::createFromFormat($format, $this->getValue());
        if (!$date) return false;
        return $date <= $compare;
    }

    /**
     * @param string $regex
     * @return bool
     */
    public function matches(string $regex): bool
    {
        return !!preg_match($regex, $this->getValue());
    }

    /**
     * @param $format
     * @return string
     */
    public function getDate($format): string {
        return get_date($format, $this->getValue(), $this->getValue());
    }

    /**
     * @return string
     */
    public function getAge(): string {
        if (!$this->isDate("m/d/Y")) return $this->getValue();
        try {
            $date = new DateTime($this->getValue());
            $to = new DateTime('today');
        } catch (Exception $e) {
            return $this->getValue();
        }
        $age = (int) $date->diff($to)->y;
        return ($age >= 2 ? "{$age} years old" : "{$age} year old");
    }

    /**
     * @return bool
     */
    public function isBool(): bool {
        return in_array($this->getValue(), [true, false, 0, 1, '0', '1'], true);
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
        return str_word_count($this->getValue()) > $max;
    }

    /**
     * @param int $min
     * @return bool
     */
    public function hasMinWord(int $min = 1): bool {
        return str_word_count($this->getValue()) < $min;
    }

    /**
     * @param int $max
     * @return bool
     */
    public function hasMaxLength(int $max): bool {
        return strlen($this->getValue()) > $max;
    }

    /**
     * @param int $min
     * @return bool
     */
    public function hasMinLength(int $min = 1): bool {
        return strlen($this->getValue()) < $min;
    }

    /**
     * @param string $algo
     * @return Condition
     */
    public function hash(string $algo): ConditionAlias {
        $this->setValue(hash($algo, $this->getValue()));
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
     * @return $this
     */
    public function trim(): ConditionAlias {
        $this->setValue(trim($this->getValue()));
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