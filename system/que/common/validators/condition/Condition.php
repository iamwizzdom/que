<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/10/2018
 * Time: 2:28 PM
 */

namespace que\common\validator\condition;

use Closure;
use DateTime;
use Exception;
use que\common\validator\interfaces\Condition as ConditionAlias;
use que\database\interfaces\Builder;
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
     * @param int $number1
     * @param int $number2
     * @return bool
     */
    public function isNumberBetween(int $number1, int $number2): bool
    {
        // TODO: Implement isNumberBetween() method.
        return $this->getValue() >= $number1 && $this->getValue() <= $number2;
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
    public function isFloatingNumberGreaterThan(float $number): bool
    {
        // TODO: Implement isFloatingNumberGreaterThan() method.
        return $this->isFloatingNumber() && ($this->getValue() > $number);
    }

    /**
     * @inheritDoc
     */
    public function isFloatingNumberGreaterThanOrEqual(float $number): bool
    {
        // TODO: Implement isFloatingNumberGreaterThanOrEqual() method.
        return $this->isFloatingNumber() && ($this->getValue() >= $number);
    }

    /**
     * @inheritDoc
     */
    public function isFloatingNumberLessThan(float $number): bool
    {
        // TODO: Implement isFloatingNumberLessThan() method.
        return $this->isFloatingNumber() && ($this->getValue() < $number);
    }

    /**
     * @inheritDoc
     */
    public function isFloatingNumberLessThanOrEqual(float $number): bool
    {
        // TODO: Implement isFloatingNumberLessThanOrEqual() method.
        return $this->isFloatingNumber() && ($this->getValue() <= $number);
    }

    /**
     * @param float $number1
     * @param float $number2
     * @return bool
     */
    public function isFloatingNumberBetween(float $number1, float $number2): bool
    {
        // TODO: Implement isFloatingNumberBetween() method.
        return $this->getValue() >= $number1 && $this->getValue() <= $number2;
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
        return preg_match("/^(\+)?[0-9]{4,15}+$/", $this->getValue()) == 1;
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
        return $this->isNotFoundInDB($table, $column, null, $ignoreID, $ignoreColumn);
    }

    /**
     * @inheritDoc
     */
    public function isFoundInDB($table, $column, ?Closure $extraQuery = null,
                                $ignoreID = null, string $ignoreColumn = 'id'): bool
    {
        // TODO: Implement isFoundInDB() method.
        return db()->check($table, function (Builder $builder) use ($table, $column, $extraQuery, $ignoreID, $ignoreColumn) {
            $builder->where($column, $this->getValue());
            if ($ignoreID !== null) $builder->where($ignoreColumn, $ignoreID, '!=');
            if (is_callable($extraQuery)) $extraQuery($builder);
        })->isSuccessful();
    }

    /**
     * @inheritDoc
     */
    public function isNotFoundInDB($table, $column, ?Closure $extraQuery = null,
                                   $ignoreID = null, string $ignoreColumn = 'id'): bool
    {
        // TODO: Implement isNotFoundInDB() method.
        return !$this->isFoundInDB($table, $column, $extraQuery, $ignoreID, $ignoreColumn);
    }

    /**
     * @param string|null $format
     * @return bool
     */
    public function isDate(?string $format = null): bool {
        if ($format) {
            $date = DateTime::createFromFormat($format, $this->getValue());
            $errors = DateTime::getLastErrors();
            if (!empty($errors['warnings'] ?? [])) $date = null;
            elseif (!empty($errors['errors'] ?? [])) $date = null;
        } else {
            try {
                $date = new DateTime($this->getValue());
            } catch (Exception $e) {
                $date = null;
            }
        }
        return $date instanceof DateTime;
    }

    /**
     * @param DateTime $compare
     * @param string|null $format
     * @return bool
     */
    public function isDateEqual(DateTime $compare, ?string $format = null): bool
    {
        // TODO: Implement isDateEqual() method.
        if ($format) {
            $date = DateTime::createFromFormat($format, $this->getValue());
            $errors = DateTime::getLastErrors();
            if (!empty($errors['warnings'] ?? [])) $date = null;
            elseif (!empty($errors['errors'] ?? [])) $date = null;
        } else {
            try {
                $date = new DateTime($this->getValue());
            } catch (Exception $e) {
                $date = null;
            }
        }
        return $date instanceof DateTime && $date->getTimestamp() == $compare->getTimestamp();
    }

    /**
     * @param DateTime $compare
     * @param string|null $format
     * @return bool
     */
    public function isDateNotEqual(DateTime $compare, ?string $format = null): bool
    {
        // TODO: Implement isDateNotEqual() method.
        return !$this->isDateEqual($compare, $format);
    }


    /**
     * @param DateTime $compare
     * @param string|null $format
     * @return bool
     */
    public function isDateGreaterThan(DateTime $compare, ?string $format = null): bool {
        if ($format) {
            $date = DateTime::createFromFormat($format, $this->getValue());
            $errors = DateTime::getLastErrors();
            if (!empty($errors['warnings'] ?? [])) $date = null;
            elseif (!empty($errors['errors'] ?? [])) $date = null;
        } else {
            try {
                $date = new DateTime($this->getValue());
            } catch (Exception $e) {
                $date = null;
            }
        }
        return $date instanceof DateTime && $date->getTimestamp() > $compare->getTimestamp();
    }

    /**
     * @param DateTime $compare
     * @param string|null $format
     * @return bool
     */
    public function isDateGreaterThanOrEqual(DateTime $compare, ?string $format = null): bool {
        if ($format) {
            $date = DateTime::createFromFormat($format, $this->getValue());
            $errors = DateTime::getLastErrors();
            if (!empty($errors['warnings'] ?? [])) $date = null;
            elseif (!empty($errors['errors'] ?? [])) $date = null;
        } else {
            try {
                $date = new DateTime($this->getValue());
            } catch (Exception $e) {
                $date = null;
            }
        }
        return $date instanceof DateTime && $date->getTimestamp() >= $compare->getTimestamp();
    }

    /**
     * @param DateTime $compare
     * @param string|null $format
     * @return bool
     */
    public function isDateLessThan(DateTime $compare, ?string $format = null): bool {
        if ($format) {
            $date = DateTime::createFromFormat($format, $this->getValue());
            $errors = DateTime::getLastErrors();
            if (!empty($errors['warnings'] ?? [])) $date = null;
            elseif (!empty($errors['errors'] ?? [])) $date = null;
        } else {
            try {
                $date = new DateTime($this->getValue());
            } catch (Exception $e) {
                $date = null;
            }
        }
        return $date instanceof DateTime && $date->getTimestamp() < $compare->getTimestamp();
    }

    /**
     * @param DateTime $compare
     * @param string|null $format
     * @return bool
     */
    public function isDateLessThanOrEqual(DateTime $compare, ?string $format = null): bool {
        if ($format) {
            $date = DateTime::createFromFormat($format, $this->getValue());
            $errors = DateTime::getLastErrors();
            if (!empty($errors['warnings'] ?? [])) $date = null;
            elseif (!empty($errors['errors'] ?? [])) $date = null;
        } else {
            try {
                $date = new DateTime($this->getValue());
            } catch (Exception $e) {
                $date = null;
            }
        }
        return $date instanceof DateTime && $date->getTimestamp() <= $compare->getTimestamp();
    }

    /**
     * @param DateTime $date1
     * @param DateTime $date2
     * @param string|null $format
     * @return bool
     */
    public function isDateBetween(DateTime $date1, DateTime $date2, ?string $format = null): bool
    {
        // TODO: Implement isDateBetween() method.
        if ($format) {
            $date = DateTime::createFromFormat($format, $this->getValue());
            $errors = DateTime::getLastErrors();
            if (!empty($errors['warnings'] ?? [])) $date = null;
            elseif (!empty($errors['errors'] ?? [])) $date = null;
        } else {
            try {
                $date = new DateTime($this->getValue());
            } catch (Exception $e) {
                $date = null;
            }
        }
        return $date instanceof DateTime && $date->getTimestamp() >= $date1->getTimestamp() &&
            $date->getTimestamp() <= $date2->getTimestamp();
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
     * @param int $size
     * @return bool
     */
    public function hasWord(int $size): bool
    {
        // TODO: Implement hasWord() method.
        return str_word_count($this->getValue(), 0) == $size;
    }


    /**
     * @param int $max
     * @return bool
     */
    public function hasMaxWord(int $max): bool {
        return str_word_count($this->getValue(), 0) <= $max;
    }

    /**
     * @param int $min
     * @return bool
     */
    public function hasMinWord(int $min = 1): bool {
        return str_word_count($this->getValue(), 0) >= $min;
    }

    /**
     * @param int $size
     * @return bool
     */
    public function hasLength(int $size): bool
    {
        // TODO: Implement hasLength() method.
        return strlen($this->getValue()) == $size;
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
    public function hash(string $algo = "SHA256"): Condition {
        $this->setValue(Hash::sha($this->getValue(), $algo));
        return $this;
    }

    /**
     * @return $this
     */
    public function toUpper(): Condition {
        $this->setValue(strtoupper($this->getValue()));
        return $this;
    }

    /**
     * @return $this
     */
    public function toLower(): Condition {
        $this->setValue(strtolower($this->getValue()));
        return $this;
    }

    /**
     * @return $this
     */
    public function toUcFirst(): Condition {
        $this->setValue(ucfirst($this->getValue()));
        return $this;
    }

    /**
     * @return $this
     */
    public function toUcWords(): Condition {
        $this->setValue(ucwords($this->getValue()));
        return $this;
    }

    /**
     * @param string $charlist
     * @return $this
     */
    public function trim(string $charlist = " \t\n\r\0\x0B"): Condition {
        $this->setValue(trim($this->getValue(), $charlist));
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toDate(string $format): Condition
    {
        // TODO: Implement toDateFormat() method.
        $this->setValue(get_date($format, $this->getValue(), $this->getValue()));
        return $this;
    }

    /**
     * @param $function
     * @param mixed ...$parameter
     * @note Due to the fact that the subject parameter position might vary across functions,
     * provision has been made for you to define the subject parameter with the key ":subject".
     * e.g to run a function like explode, you are to invoke it as follows: _call('explode', 'delimiter', ':subject');
     * @return Condition
     */
    public function _call($function, ...$parameter): Condition {
        if (!function_exists($function)) return $this;
        if (!empty($parameter)) {
            $key = array_search(":subject", $parameter);
            if ($key !== false) $parameter[$key] = $this->getValue();
        } else $parameter = [$this->getValue()];
        $this->setValue(call_user_func($function, ...$parameter));
        return $this;
    }
}
