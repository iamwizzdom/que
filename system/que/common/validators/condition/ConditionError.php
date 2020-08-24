<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 8/12/2018
 * Time: 10:06 PM
 */

namespace que\common\validator\condition;

use DateTime;
use que\common\validator\Validator;

class ConditionError
{
    private Condition $condition;

    /**
     * @var string|null
     */
    private ?string $error = null;

    /**
     * @var Validator
     */
    private Validator $validator;

    /**
     * @var bool
     */
    private bool $nullable;

    /**
     * Condition constructor.
     * @param $key
     * @param $value
     * @param Validator $validator
     * @param bool $nullable
     */
    public function __construct($key, $value, Validator $validator, $nullable = false)
    {
        $this->condition = new Condition($key, $value);
        $this->setValidator($validator);
        $this->nullable = $nullable;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->condition->getKey();
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->condition->getValue();
    }

    /**
     * @param string $value
     */
    private function setValue($value)
    {
        $this->getValidator()->setValue($this->getKey(), $value);
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
     * @return ConditionError
     */
    public static function clone($key, $value, Validator $validator): ConditionError
    {
        return new ConditionError($key, $value, $validator);
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @param $error
     * @return ConditionError
     */
    public function setError($error): ConditionError
    {
        $this->error = $error !== null ? $error : "Value for '{$this->getKey()}' does not seem to be valid when '{$this->getValue()}' value is given";
        return $this;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->error !== null;
    }

    /**
     * @param null $error
     * @return ConditionError
     */
    public function isNotEmpty($error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isNotEmpty()) $this->setError($error);
        return $this;
    }

    /**
     * @param null $error
     * @return ConditionError
     */
    public function isEmpty($error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isEmpty()) $this->setError($error);
        return $this;
    }

    /**
     * @param null $error
     * @param string|null $pattern
     * @return ConditionError
     */
    public function isUrl($error = null, string $pattern = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isUrl($pattern)) $this->setError($error);
        return $this;
    }

    /**
     * @param null $error
     * @return ConditionError
     */
    public function isString($error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isString()) $this->setError($error);
        return $this;
    }

    /**
     * @param null $error
     * @return ConditionError
     */
    public function isUsername($error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isUsername()) $this->setError($error);
        return $this;
    }

    /**
     * @param null $error
     * @return ConditionError
     */
    public function isChar($error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isChar()) $this->setError($error);
        return $this;
    }

    /**
     * @param null $error
     * @return ConditionError
     */
    public function isAlphaNumeric($error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isAlphaNumeric()) $this->setError($error);
        return $this;
    }

    /**
     * @param null $error
     * @return ConditionError
     */
    public function isEmail($error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isEmail()) $this->setError($error);
        return $this;
    }

    /**
     * @param null $error
     * @return ConditionError
     */
    public function isUUID($error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isUUID()) $this->setError($error);
        return $this;
    }

    /**
     * @param $table
     * @param $column
     * @param null $error
     * @param null $ignoreID
     * @param string $ignoreColumn
     * @return ConditionError
     */
    public function isUniqueInDB($table, $column, $error = null, $ignoreID = null,
                                 string $ignoreColumn = 'id'): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isUniqueInDB($table, $column, $ignoreID, $ignoreColumn)) $this->setError($error);
        return $this;
    }

    /**
     * @param $table
     * @param $column
     * @param bool $considerIsActive
     * @param null $error
     * @param null $ignoreID
     * @param string $ignoreColumn
     * @return ConditionError
     */
    public function isFoundInDB($table, $column, bool $considerIsActive = false, $error = null,
                                $ignoreID = null, string $ignoreColumn = 'id'): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isFoundInDB($table, $column, $considerIsActive,
            $ignoreID, $ignoreColumn)) $this->setError($error);
        return $this;
    }

    /**
     * @param $table
     * @param $column
     * @param bool $considerIsActive
     * @param null $error
     * @param null $ignoreID
     * @param string $ignoreColumn
     * @return ConditionError
     */
    public function isNotFoundInDB($table, $column, bool $considerIsActive = false, $error = null,
                                   $ignoreID = null, string $ignoreColumn = 'id'): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isNotFoundInDB($table, $column, $considerIsActive,
            $ignoreID, $ignoreColumn)) $this->setError($error);
        return $this;
    }

    /**
     * @param null $error
     * @param $format
     * @return $this
     */
    public function isDate($error = null, $format = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isDate($format)) $this->setError($error);
        return $this;
    }

    /**
     * @param DateTime $compare
     * @param null $error
     * @param string|null $format
     * @return $this
     */
    public function isDateGreaterThan(DateTime $compare, $error = null, ?string $format = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isDateGreaterThan($compare, $format)) $this->setError($error);
        return $this;
    }

    /**
     * @param DateTime $compare
     * @param null $error
     * @param string|null $format
     * @return $this
     */
    public function isDateGreaterThanOrEqual(DateTime $compare, $error = null, ?string $format = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isDateGreaterThanOrEqual($compare, $format)) $this->setError($error);
        return $this;
    }

    /**
     * @param DateTime $compare
     * @param null $error
     * @param string|null $format
     * @return $this
     */
    public function isDateLessThan(DateTime $compare, $error = null, ?string $format = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isDateLessThan($compare, $format)) $this->setError($error);
        return $this;
    }

    /**
     * @param DateTime $compare
     * @param null $error
     * @param string|null $format
     * @return $this
     */
    public function isDateLessThanOrEqual(DateTime $compare, $error = null, ?string $format = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isDateLessThanOrEqual($compare, $format)) $this->setError($error);
        return $this;
    }

    /**
     * @param $variable
     * @param null $error
     * @return ConditionError
     */
    public function isIdentical($variable, $error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isIdentical($variable)) $this->setError($error);
        return $this;
    }

    /**
     * @param $variable
     * @param null $error
     * @return ConditionError
     */
    public function isNotIdentical($variable, $error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isNotIdentical($variable)) $this->setError($error);
        return $this;
    }

    /**
     * @param array $variable
     * @param null $error
     * @return ConditionError
     */
    public function isIdenticalToAny(array $variable, $error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isIdenticalToAny($variable)) $this->setError($error);
        return $this;
    }

    /**
     * @param array $variable
     * @param null $error
     * @return ConditionError
     */
    public function isNotIdenticalToAny(array $variable, $error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isNotIdenticalToAny($variable)) $this->setError($error);
        return $this;
    }

    /**
     * @param array $variable
     * @param null $error
     * @return ConditionError
     */
    public function isEqualToAny(array $variable, $error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isEqualToAny($variable)) $this->setError($error);
        return $this;
    }

    /**
     * @param array $variable
     * @param null $error
     * @return ConditionError
     */
    public function isNotEqualToAny(array $variable, $error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isNotEqualToAny($variable)) $this->setError($error);
        return $this;
    }

    /**
     * @param $variable
     * @param null $error
     * @return ConditionError
     */
    public function isEqual($variable, $error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isEqual($variable)) $this->setError($error);
        return $this;
    }

    /**
     * @param $variable
     * @param null $error
     * @return ConditionError
     */
    public function isNotEqual($variable, $error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isNotEqual($variable)) $this->setError($error);
        return $this;
    }

    /**
     * @param null $error
     * @return ConditionError
     */
    public function isNumberFormat($error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isNumberFormat()) $this->setError($error);
        return $this;
    }

    /**
     * @param null $error
     * @return ConditionError
     */
    public function isNumber($error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isNumber()) $this->setError($error);
        return $this;
    }

    /**
     * @param int $number
     * @param null $error
     * @return ConditionError
     */
    public function isNumberGreaterThan(int $number, $error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isNumberGreaterThan($number))
            $this->setError(($error && str_contains($error, "%s") ? sprintf($error, $number) : $error));
        return $this;
    }

    /**
     * @param int $number
     * @param null $error
     * @return ConditionError
     */
    public function isNumberGreaterThanOrEqual(int $number, $error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isNumberGreaterThanOrEqual($number))
            $this->setError(($error && str_contains($error, "%s") ? sprintf($error, $number) : $error));
        return $this;
    }

    /**
     * @param int $number
     * @param null $error
     * @return ConditionError
     */
    public function isNumberLessThan(int $number, $error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isNumberLessThan($number))
            $this->setError(($error && str_contains($error, "%s") ? sprintf($error, $number) : $error));
        return $this;
    }

    /**
     * @param int $number
     * @param null $error
     * @return ConditionError
     */
    public function isNumberLessThanOrEqual(int $number, $error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isNumberLessThanOrEqual($number))
            $this->setError(($error && str_contains($error, "%s") ? sprintf($error, $number) : $error));
        return $this;
    }

    /**
     * @param null $error
     * @return ConditionError
     */
    public function isFloatingNumber($error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isFloatingNumber()) $this->setError($error);
        return $this;
    }

    /**
     * @param int $number
     * @param null $error
     * @return ConditionError
     */
    public function isFloatingNumberGreaterThan(int $number, $error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isFloatingNumberGreaterThan($number))
            $this->setError(($error && str_contains($error, "%s") ? sprintf($error, $number) : $error));
        return $this;
    }

    /**
     * @param int $number
     * @param null $error
     * @return ConditionError
     */
    public function isFloatingNumberGreaterThanOrEqual(int $number, $error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isFloatingNumberGreaterThanOrEqual($number))
            $this->setError(($error && str_contains($error, "%s") ? sprintf($error, $number) : $error));
        return $this;
    }

    /**
     * @param int $number
     * @param null $error
     * @return ConditionError
     */
    public function isFloatingNumberLessThan(int $number, $error = null): ConditionError
    {
        if (!$this->condition->isFloatingNumberLessThan($number))
            $this->setError(($error && str_contains($error, "%s") ? sprintf($error, $number) : $error));
        return $this;
    }

    /**
     * @param int $number
     * @param null $error
     * @return ConditionError
     */
    public function isFloatingNumberLessThanOrEqual(int $number, $error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isFloatingNumberLessThanOrEqual($number))
            $this->setError(($error && str_contains($error, "%s") ? sprintf($error, $number) : $error));
        return $this;
    }

    /**
     * @param null $error
     * @return ConditionError
     */
    public function isPhoneNumber($error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isPhoneNumber()) $this->setError($error);
        return $this;
    }

    /**
     * @param null $error
     * @return ConditionError
     */
    public function isInteger($error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isInteger()) $this->setError($error);
        return $this;
    }

    /**
     * @param null $error
     * @return ConditionError
     */
    public function isBool($error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isBool()) $this->setError($error);
        return $this;
    }

    /**
     * @param callable $test
     * @param null $error
     * @return ConditionError
     */
    public function isTrue(callable $test, $error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isTrue($test)) $this->setError($error);
        return $this;
    }

    /**
     * @param callable $test
     * @param null $error
     * @return ConditionError
     */
    public function isFalse(callable $test, $error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isFalse($test)) $this->setError($error);
        return $this;
    }

    /**
     * @param string $regex
     * @param $error
     * @return ConditionError
     */
    public function matches(string $regex, $error): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->matches($regex)) $this->setError($error);
        return $this;
    }

    /**
     * @param null $error
     * @return ConditionError
     */
    public function isIp($error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isIp()) $this->setError($error);
        return $this;
    }

    /**
     * @param null $error
     * @return ConditionError
     */
    public function isIpv4($error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isIpv4()) $this->setError($error);
        return $this;
    }

    /**
     * @param null $error
     * @return ConditionError
     */
    public function isIpv4NoPriv($error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isIpv4NoPriv()) $this->setError($error);
        return $this;
    }

    /**
     * @param null $error
     * @return ConditionError
     */
    public function isIpv6($error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isIpv6()) $this->setError($error);
        return $this;
    }

    /**
     * @param null $error
     * @return ConditionError
     */
    public function isIpv6NoPriv($error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->isIpv6NoPriv()) $this->setError($error);
        return $this;
    }

    /**
     * @param $max
     * @param null $error
     * @return ConditionError
     */
    public function hasMaxWord($max, $error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->hasMaxWord($max))
            $this->setError(($error && str_contains($error, "%s") ? sprintf($error, $max) : $error));
        return $this;
    }

    /**
     * @param int $min
     * @param null $error
     * @return ConditionError
     */
    public function hasMinWord($min = 3, $error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->hasMinWord($min))
            $this->setError(($error && str_contains($error, "%s") ? sprintf($error, $min) : $error));
        return $this;
    }

    /**
     * @param $max
     * @param null $error
     * @return ConditionError
     */
    public function hasMaxLength($max, $error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->hasMaxLength($max))
            $this->setError(($error && str_contains($error, "%s") ? sprintf($error, $max) : $error));
        return $this;
    }

    /**
     * @param int $min
     * @param null $error
     * @return ConditionError
     */
    public function hasMinLength($min = 3, $error = null): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        if (!$this->condition->hasMinLength($min))
            $this->setError(($error && str_contains($error, "%s") ? sprintf($error, $min) : $error));
        return $this;
    }

    /**
     * @param string $algo
     * @return $this
     */
    public function hash(string $algo = "SHA256"): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        $this->condition->hash($algo);
        $this->setValue($this->getValue());
        return $this;
    }

    /**
     * @return $this
     */
    public function toUpper(): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        $this->condition->toUpper();
        $this->setValue($this->getValue());
        return $this;
    }

    /**
     * @return $this
     */
    public function toLower(): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        $this->condition->toLower();
        $this->setValue($this->getValue());
        return $this;
    }

    /**
     * @return $this
     */
    public function toUcFirst(): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        $this->condition->toUcFirst();
        $this->setValue($this->getValue());
        return $this;
    }

    /**
     * @return $this
     */
    public function toUcWords(): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        $this->condition->toUcWords();
        $this->setValue($this->getValue());
        return $this;
    }

    /**
     * @param string $charlist
     * @return $this
     */
    public function trim(string $charlist = " \t\n\r\0\x0B"): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        $this->condition->trim($charlist);
        $this->setValue($this->getValue());
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toDate($format): ConditionError
    {
        // TODO: Implement toDateFormat() method.
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        $this->condition->toDate($format);
        $this->setValue($this->getValue());
        return $this;
    }

    /**
     * @param $function
     * @param array $parameter
     * @return ConditionError
     */
    public function _call($function, ...$parameter): ConditionError
    {
        if ($this->hasError() || ($this->nullable && empty($this->getValue()))) return $this;
        $this->condition->_call($function, $parameter);
        $this->setValue($this->getValue());
        return $this;
    }
}
