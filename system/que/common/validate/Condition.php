<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 8/12/2018
 * Time: 10:06 PM
 */

namespace que\common\validate;

use DateTime;
use que\utility\client\IP;

class Condition
{

    /**
     * @var string
     */
    private $key;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var array
     */
    private $error = array();

    /**
     * @var Validator
     */
    private $validator;

    /**
     * Condition constructor.
     * @param $key
     * @param $value
     * @param Validator $validator
     */
    public function __construct($key, $value, Validator $validator)
    {
        $this->setValidator($validator);
        $this->setKey($key);
        $this->setValue($value);
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
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
     * @param string $value
     */
    private function setValue($value)
    {
        $this->value = $value;
        $this->getValidator()->setValue(
            $this->getKey(), $value);
    }

    /**
     * @return Validator
     */
    public function getValidator(): Validator
    {
        return $this->validator;
    }

    /**
     * @param Validator $validator
     */
    private function setValidator(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     *
     * @param string $key
     * @param mixed $value
     * @param Validator $validator
     * @return Condition
     */
    public static function clone($key, $value, Validator $validator): Condition
    {
        return new Condition($key, $value, $validator);
    }

    /**
     * @return array
     */
    public function getError(): array
    {
        return $this->error;
    }

    /**
     * @param $error
     * @return Condition
     */
    public function addError($error): Condition
    {
        if ($error === null)
            $error = "Value for '{$this->key}' does not seem to be valid when '{$this->getValue()}' value is given";
        array_push($this->error, $error);
        return $this;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return count($this->error) > 0;
    }

    /**
     * @param null $error
     * @return Condition
     */
    public function isNotBlank($error = null): Condition
    {
        if (empty($this->getValue()) && $this->getValue() != "0") $this->addError($error);

        return $this;
    }

    /**
     * @param null $error
     * @param string|null $pattern
     * @return Condition
     */
    public function isUrl($error = null, string $pattern = null): Condition
    {
        $filter = filter_var($this->getValue(), FILTER_VALIDATE_URL);

        if (!is_null($pattern) && (!$filter || !preg_match($pattern, $this->getValue())))
            $this->addError($error);
        elseif (!$filter) $this->addError($error);

        return $this;
    }

    /**
     * @param null $error
     * @return Condition
     */
    public function isString($error = null): Condition
    {
        if (!is_string($this->getValue()))
            $this->addError($error);

        return $this;
    }

    /**
     * @param null $error
     * @return Condition
     */
    public function isUsername($error = null): Condition
    {
        if (!preg_match("/^[a-zA-Z0-9._]+$/", $this->getValue()))
            $this->addError($error);

        return $this;
    }

    /**
     * @param null $error
     * @return Condition
     */
    public function isChar($error = null): Condition
    {
        if (!preg_match("/^[a-zA-Z]+$/", $this->getValue()))
            $this->addError($error);

        return $this;
    }

    /**
     * @param null $error
     * @return Condition
     */
    public function isAlphaNumeric($error = null): Condition
    {
        if (!preg_match("/^(?=.*\d)(?=.*[a-zA-Z])/", $this->getValue()))
            $this->addError($error);

        return $this;
    }

    /**
     * @param null $error
     * @return Condition
     */
    public function isEmail($error = null): Condition
    {
        if (!filter_var($this->getValue(), FILTER_VALIDATE_EMAIL))
            $this->addError($error);

        return $this;
    }

    /**
     * @param $table
     * @param $column
     * @param null $error
     * @return Condition
     */
    public function isUniqueInDB($table, $column, $error = null): Condition
    {
        if ((db()->check($table, [
            'AND' => [
                $column => $this->getValue()
            ]
        ])->isSuccessful())) $this->addError($error);

        return $this;
    }

    /**
     * @param $table
     * @param $column
     * @param bool $considerIsActive
     * @param null $error
     * @return Condition
     */
    public function isFoundInDB($table, $column, bool $considerIsActive = false, $error = null): Condition
    {
        if (!(db()->check($table, [
            'AND' => ($considerIsActive === true ? [
                $column => $this->getValue(),
                (CONFIG['db_table_status_key'] ?? 'is_active') => STATE_ACTIVE
            ] : [
                $column => $this->getValue()
            ])
        ])->isSuccessful())) $this->addError($error);

        return $this;
    }

    /**
     * @param $table
     * @param $column
     * @param bool $considerIsActive
     * @param null $error
     * @return Condition
     */
    public function isNotFoundInDB($table, $column, bool $considerIsActive = false, $error = null): Condition
    {
        if ((db()->check($table, [
            'AND' => ($considerIsActive === true ? [
                $column => $this->getValue(),
                (CONFIG['db_table_status_key'] ?? 'is_active') => STATE_ACTIVE
            ] : [
                $column => $this->getValue()
            ])
        ])->isSuccessful())) $this->addError($error);

        return $this;
    }

    /**
     * @param $format
     * @param null $error
     * @return Condition
     */
    public function isDate($format, $error = null): Condition
    {
        $date = DateTime::createFromFormat($format, $this->getValue());
        if (!$date) $this->addError($error);

        return $this;
    }

    /**
     * @param $format
     * @param DateTime $compare
     * @param null $error
     * @return Condition
     */
    public function isDateGreaterThan($format, DateTime $compare, $error = null): Condition
    {
        $date = DateTime::createFromFormat($format, $this->getValue());
        if (!$date) $this->addError($error);
        elseif (!($date > $compare)) $this->addError($error);

        return $this;
    }

    /**
     * @param $format
     * @param DateTime $compare
     * @param null $error
     * @return Condition
     */
    public function isDateGreaterThanOrEqual($format, DateTime $compare, $error = null): Condition
    {
        $date = DateTime::createFromFormat($format, $this->getValue());
        if (!$date) $this->addError($error);
        elseif (!($date >= $compare)) $this->addError($error);

        return $this;
    }

    /**
     * @param $format
     * @param DateTime $compare
     * @param null $error
     * @return Condition
     */
    public function isDateLessThan($format, DateTime $compare, $error = null): Condition
    {
        $date = DateTime::createFromFormat($format, $this->getValue());
        if (!$date) $this->addError($error);
        elseif (!($date < $compare)) $this->addError($error);

        return $this;
    }

    /**
     * @param $format
     * @param DateTime $compare
     * @param null $error
     * @return Condition
     */
    public function isDateLessThanOrEqual($format, DateTime $compare, $error = null): Condition
    {
        $date = DateTime::createFromFormat($format, $this->getValue());
        if (!$date) $this->addError($error);
        elseif (!($date <= $compare)) $this->addError($error);

        return $this;
    }

    /**
     * @param $variable
     * @param null $error
     * @return Condition
     */
    public function isIdentical($variable, $error = null): Condition
    {
        if (is_array($variable)) {

            foreach ($variable as $value)
                if ($value !== $this->getValue())
                    $this->addError($error);

        } elseif ($variable !== $this->getValue())
            $this->addError($error);

        return $this;
    }

    /**
     * @param $variable
     * @param null $error
     * @return Condition
     */
    public function isNotIdentical($variable, $error = null): Condition
    {
        if (is_array($variable)) {

            foreach ($variable as $value)
                if ($value === $this->getValue())
                    $this->addError($error);

        } elseif ($variable === $this->getValue())
            $this->addError($error);

        return $this;
    }

    /**
     * @param array $variable
     * @param null $error
     * @return Condition
     */
    public function isEqualToAny(array $variable, $error = null): Condition
    {
        if (!in_array($this->getValue(), $variable))
            $this->addError($error);

        return $this;
    }

    /**
     * @param array $variable
     * @param null $error
     * @return Condition
     */
    public function isNotEqualToAny(array $variable, $error = null): Condition
    {
        if (in_array($this->getValue(), $variable))
            $this->addError($error);

        return $this;
    }

    /**
     * @param $variable
     * @param null $error
     * @return Condition
     */
    public function isEqual($variable, $error = null): Condition
    {
        if (is_array($variable)) {

            foreach ($variable as $value)
                if ($value != $this->getValue())
                    $this->addError($error);

        } elseif ($variable != $this->getValue())
            $this->addError($error);

        return $this;
    }

    /**
     * @param $variable
     * @param null $error
     * @return Condition
     */
    public function isNotEqual($variable, $error = null): Condition
    {
        if (is_array($variable)) {

            foreach ($variable as $value)
                if ($value == $this->getValue())
                    $this->addError($error);

        } elseif ($variable == $this->getValue())
            $this->addError($error);

        return $this;
    }

    /**
     * @param null $error
     * @return Condition
     */
    public function isNumberFormat($error = null): Condition
    {
        if (!preg_match("/^[0-9.,]+$/", $this->getValue()))
            $this->addError($error);

        return $this;
    }

    /**
     * @param null $error
     * @return Condition
     */
    public function isNumber($error = null): Condition
    {
        if (!preg_match("/^[0-9]+$/", $this->getValue()))
            $this->addError($error);

        return $this;
    }

    /**
     * @param int $number
     * @param null $error
     * @return Condition
     */
    public function isNumberGreaterThan(int $number, $error = null): Condition
    {

        if (!preg_match("/^[0-9]+$/", $this->getValue()))
            $this->addError($error);
        elseif (!($this->getValue() > $number)) $this->addError($error);

        return $this;
    }

    /**
     * @param int $number
     * @param null $error
     * @return Condition
     */
    public function isNumberGreaterThanOrEqual(int $number, $error = null): Condition
    {

        if (!preg_match("/^[0-9]+$/", $this->getValue()))
            $this->addError($error);
        elseif (!($this->getValue() >= $number)) $this->addError($error);

        return $this;
    }

    /**
     * @param int $number
     * @param null $error
     * @return Condition
     */
    public function isNumberLessThan(int $number, $error = null): Condition
    {
        if (!preg_match("/^[0-9]+$/", $this->getValue()))
            $this->addError($error);
        elseif (!($this->getValue() < $number)) $this->addError($error);

        return $this;
    }

    /**
     * @param int $number
     * @param null $error
     * @return Condition
     */
    public function isNumberLessThanOrEqual(int $number, $error = null): Condition
    {
        if (!preg_match("/^[0-9]+$/", $this->getValue()))
            $this->addError($error);
        elseif (!($this->getValue() <= $number)) $this->addError($error);

        return $this;
    }

    /**
     * @param null $error
     * @return Condition
     */
    public function isFloatingNumber($error = null): Condition
    {
        if (!filter_var($this->getValue(), FILTER_VALIDATE_FLOAT))
            $this->addError($error);

        return $this;
    }

    /**
     * @param int $number
     * @param null $error
     * @return Condition
     */
    public function isFloatingNumberGreaterThan(int $number, $error = null): Condition
    {

        if (!filter_var($this->getValue(), FILTER_VALIDATE_FLOAT))
            $this->addError($error);
        elseif (!($this->getValue() > $number)) $this->addError($error);

        return $this;
    }

    /**
     * @param int $number
     * @param null $error
     * @return Condition
     */
    public function isFloatingNumberGreaterThanOrEqual(int $number, $error = null): Condition
    {

        if (!filter_var($this->getValue(), FILTER_VALIDATE_FLOAT))
            $this->addError($error);
        elseif (!($this->getValue() >= $number)) $this->addError($error);

        return $this;
    }

    /**
     * @param int $number
     * @param null $error
     * @return Condition
     */
    public function isFloatingNumberLessThan(int $number, $error = null): Condition
    {
        if (!filter_var($this->getValue(), FILTER_VALIDATE_FLOAT))
            $this->addError($error);
        elseif (!($this->getValue() < $number)) $this->addError($error);

        return $this;
    }

    /**
     * @param int $number
     * @param null $error
     * @return Condition
     */
    public function isFloatingNumberLessThanOrEqual(int $number, $error = null): Condition
    {
        if (!filter_var($this->getValue(), FILTER_VALIDATE_FLOAT))
            $this->addError($error);
        elseif (!($this->getValue() <= $number)) $this->addError($error);

        return $this;
    }

    /**
     * @param null $error
     * @return Condition
     */
    public function isPhoneNumber($error = null): Condition
    {
        if (!preg_match("/^[+0-9]+$/", $this->getValue()))
            $this->addError($error);

        return $this;
    }

    /**
     * @param null $error
     * @return Condition
     */
    public function isInteger($error = null): Condition
    {
        if (!preg_match("/^[0-9\-]+$/", $this->getValue()))
            $this->addError($error);

        return $this;
    }

    /**
     * @param null $error
     * @return Condition
     */
    public function isBool($error = null): Condition
    {
        if (!in_array($this->getValue(), [true, false, 0,
            1, '0', '1'], true)) $this->addError($error);

        return $this;
    }

    /**
     * @param string $regex
     * @param $error
     * @return Condition
     */
    public function matches(string $regex, $error): Condition
    {
        if (!preg_match($regex, $this->getValue()))
            $this->addError($error);

        return $this;
    }

    /**
     * @param null $error
     * @return Condition
     */
    public function isIp($error = null): Condition
    {
        if (filter_var($this->getValue(), FILTER_VALIDATE_IP) === false)
            $this->addError($error);

        return $this;
    }

    /**
     * @param null $error
     * @return Condition
     */
    public function isIpv4($error = null): Condition
    {
        if (!IP::isIpv4($this->getValue())) $this->addError($error);

        return $this;
    }

    /**
     * @param null $error
     * @return Condition
     */
    public function isIpv4NoPriv($error = null): Condition
    {
        if (!IP::isIpv4NoPriv($this->getValue())) $this->addError($error);

        return $this;
    }

    /**
     * @param null $error
     * @return Condition
     */
    public function isIpv6($error = null): Condition
    {
        if (!IP::isIpv6($this->getValue())) $this->addError($error);

        return $this;
    }

    /**
     * @param null $error
     * @return Condition
     */
    public function isIpv6NoPriv($error = null): Condition
    {
        if (!IP::isIpv6NoPriv($this->getValue())) $this->addError($error);

        return $this;
    }

    /**
     * @param $max
     * @param null $error
     * @return Condition
     */
    public function hasMaxWord($max, $error = null): Condition
    {
        if (str_word_count($this->getValue()) > $max)
            $this->addError($error);

        return $this;
    }

    /**
     * @param int $min
     * @param null $error
     * @return Condition
     */
    public function hasMinWord($min = 3, $error = null): Condition
    {
        if (str_word_count($this->getValue()) < $min)
            $this->addError($error);

        return $this;
    }

    /**
     * @param $max
     * @param null $error
     * @return Condition
     */
    public function hasMaxLength($max, $error = null): Condition
    {
        if (strlen($this->getValue()) > $max)
            $this->addError($error);

        return $this;
    }

    /**
     * @param int $min
     * @param null $error
     * @return Condition
     */
    public function hasMinLength($min = 3, $error = null): Condition
    {
        if (strlen($this->getValue()) < $min)
            $this->addError($error);

        return $this;
    }

    /**
     * @param string $algo
     * @return $this
     */
    public function hash(string $algo): Condition
    {
        if (is_array($this->getValue())) {
            $value = $this->getValue();
            foreach ($value as $key => $item)
                $value[$key] = hash($algo, $item);
            $this->setValue($value);
            return $this;
        }

        $this->setValue(hash($algo, $this->getValue()));

        return $this;
    }

    /**
     * @return $this
     */
    public function toUpper(): Condition
    {
        if (is_array($this->getValue())) {
            $value = $this->getValue();
            foreach ($value as $key => $item)
                $value[$key] = strtoupper($item);
            $this->setValue($value);
            return $this;
        }

        $this->setValue(strtoupper($this->getValue()));

        return $this;
    }

    /**
     * @return $this
     */
    public function toLower(): Condition
    {
        if (is_array($this->getValue())) {
            $value = $this->getValue();
            foreach ($value as $key => $item)
                $value[$key] = strtolower($item);
            $this->setValue($value);
            return $this;
        }

        $this->setValue(strtolower($this->getValue()));

        return $this;
    }

    /**
     * @return $this
     */
    public function toUcFirst(): Condition
    {
        if (is_array($this->getValue())) {
            $value = $this->getValue();
            foreach ($value as $key => $item)
                $value[$key] = ucfirst($item);
            $this->setValue($value);
            return $this;
        }

        $this->setValue(ucfirst($this->getValue()));

        return $this;
    }

    /**
     * @return $this
     */
    public function toUcWords(): Condition
    {
        if (is_array($this->getValue())) {
            $value = $this->getValue();
            foreach ($value as $key => $item)
                $value[$key] = ucwords($item);
            $this->setValue($value);
            return $this;
        }

        $this->setValue(ucwords($this->getValue()));

        return $this;
    }

    /**
     * @return $this
     */
    public function trim(): Condition
    {
        if (is_array($this->getValue())) {
            $value = $this->getValue();
            foreach ($value as $key => $item)
                $value[$key] = trim($item);
            $this->setValue($value);
            return $this;
        }

        $this->setValue(trim($this->getValue()));

        return $this;
    }

    /**
     * @param $function
     * @return $this
     */
    public function _call($function): Condition
    {
        if (!function_exists($function)) return $this;

        if (is_array($this->getValue())) {
            $value = $this->getValue();
            foreach ($value as $key => $item)
                $value[$key] = call_user_func($function, $item);
            $this->setValue($value);
            return $this;
        }

        $this->setValue(call_user_func($function, $this->getValue()));

        return $this;
    }
}