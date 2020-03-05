<?php


namespace que\common\validate;


use Closure;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\session\Session;

class ConditionStack
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var array
     */
    private $conditions = [];

    /**
     * @var array
     */
    private $error = array();

    /**
     * @var mixed
     */
    private $key;

    /**
     * @var array
     */
    private $value = [];

    public function __construct($key, array $value, Validator $validator)
    {
        $this->key = $key;
        $this->value = $value;
        $this->validator = $validator;
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
     * @return ConditionStack
     */
    public function addError($error): ConditionStack
    {
        if (is_null($error))
            $error = "Value for '{$this->key}' does not seem to be valid when '{$this->getValueString()}' value is given";
        array_push($this->error, $error);
        return $this;
    }

    /**
     * @param $key
     */
    public function resetKey($key) {
        $this->key = $key;
    }

    /**
     * @param array $value
     */
    public function resetValue(array $value) {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * @return array
     */
    public function getValue(): array {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getValueString(): string {
        return serializer($this->value);
    }

    /**
     * @return Validator
     */
    public function getValidator(): Validator {
        return $this->validator;
    }

    /**
     * @param Closure $callback
     * callback param 1: Condition instance
     * callback param 2: Child siblings
     * callback param 3: Child key
     * @return ConditionStack
     */
    public function validateDirectChild(Closure $callback): ConditionStack {
        foreach ($this->value as $key => $value) {
            $this->conditions[$key] = new Condition($key, $value, $this->getValidator());
            call_user_func($callback, $this->conditions[$key], array_exclude($this->value, [$key]), $key);
        }
        return $this;
    }

    /**
     * @param $childKey
     * @param Closure $callback
     * @return ConditionStack
     */
    public function validateNextedChild($childKey, Closure $callback): ConditionStack {
        foreach ($this->value as $_key => $value) {

            if (!array_key_exists($childKey, $value))
                throw new QueRuntimeException("Undefined input sub-key -- '{$childKey}'. " .
                    "Key not found under -- '{$this->getKey()}' at index '{$_key}'", "Validator error",
                    0, HTTP_INTERNAL_ERROR_CODE, PreviousException::getInstance(2));

            $this->conditions[$_key][$childKey] = new Condition($childKey, $value[$childKey], $this->getValidator());

            call_user_func($callback, $this->conditions[$_key][$childKey], array_exclude($value, [$childKey]), $_key);

        }
        return $this;
    }

    /**
     * @return bool
     */
    public function hasError(): bool {
        $hasError = false;
        foreach ($this->conditions as $conditionQueue) {
            foreach ($conditionQueue as $condition) {
                if ($condition instanceof Condition) {
                    if ($condition->hasError()) {
                        $hasError = true;
                        break;
                    }
                }
            }
        }
        return $hasError || count($this->error) > 0;
    }

    /**
     * @param int $max
     * @param null $error
     * @return ConditionStack
     */
    public function hasMaxSize($max = 3, $error = null): ConditionStack
    {
        if (array_size($this->value) > $max)
            $this->addError($error);

        return $this;
    }

    /**
     * @param int $min
     * @param null $error
     * @return ConditionStack
     */
    public function hasMinSize($min = 3, $error = null): ConditionStack
    {
        if (array_size($this->value) < $min)
            $this->addError($error);

        return $this;
    }

    /**
     * @return array
     */
    public function getErrors() {
        $errors = [];
        foreach ($this->conditions as $conditionQueue) {
            if (is_array($conditionQueue)) {
                foreach ($conditionQueue as $condition)
                    if ($condition instanceof Condition)
                        $errors[] = $condition->getError();
            } elseif ($conditionQueue instanceof Condition) {
                $condition = $conditionQueue;
                if ($condition instanceof Condition)
                    $errors = $condition->getError();
            }
        }
        return $errors;
    }

    /**
     * @return array
     */
    public function getErrorsFlat() {
        $errors = [];
        foreach ($this->conditions as $key => $conditionQueue) {
            if (is_array($conditionQueue)) {

                foreach ($conditionQueue as $condition)
                    if ($condition instanceof Condition)
                        $errors[$key][$condition->getKey()] = current($condition->getError());

            } elseif ($conditionQueue instanceof Condition) {
                $condition = $conditionQueue;
                if ($condition instanceof Condition)
                    $errors = current($condition->getError());
            }
        }
        return $errors;
    }

    /**
     * @return array
     */
    public function getStatus() {
        $status = []; $session = &Session::getInstance()->getFiles()->_get();

        foreach ($this->conditions as $key => $conditionQueue) {
            if (is_array($conditionQueue)) {

                foreach ($conditionQueue as $condition) {
                    if ($condition instanceof Condition) {
                        if ($condition->hasError()) {

                            if (!isset($session['session']['last-form-status'][$this->getKey()][$key][$condition->getKey()])) {
                                $status[$key][$condition->getKey()] = WARNING;
                                $session['session']['last-form-status'][$this->getKey()][$key][$condition->getKey()] = true;
                            } else $status[$key][$condition->getKey()] = ERROR;

                        } else {
                            $status[$key][$condition->getKey()] = SUCCESS;
                            if (isset($session['session']['last-form-status'][$this->getKey()][$key][$condition->getKey()]))
                                unset($session['session']['last-form-status'][$this->getKey()][$key][$condition->getKey()]);
                        }
                    }
                }

            } elseif ($conditionQueue instanceof Condition) {
                $condition = $conditionQueue;
                if ($condition->hasError()) {

                    if (!isset($session['session']['last-form-status'][$this->getKey()][$condition->getKey()])) {
                        $status = WARNING;
                        $session['session']['last-form-status'][$this->getKey()][$condition->getKey()] = true;
                    } else $status = ERROR;

                } else {
                    $status = SUCCESS;
                    if (isset($session['session']['last-form-status'][$this->getKey()][$condition->getKey()]))
                        unset($session['session']['last-form-status'][$this->getKey()][$condition->getKey()]);
                }
            }
        }

        return $status;
    }

}