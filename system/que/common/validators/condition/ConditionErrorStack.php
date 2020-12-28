<?php


namespace que\common\validator\condition;


use Closure;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\common\validator\Validator;
use que\http\HTTP;
use que\support\Arr;

class ConditionErrorStack
{
    /**
     * @var Validator
     */
    private Validator $validator;

    /**
     * @var ConditionError[]|ConditionErrorStack[]
     */
    private array $conditions = [];

    /**
     * @var mixed
     */
    private $key;

    /**
     * @var array
     */
    private array $value = [];

    /**
     * @var bool
     */
    private bool $nullable;

    /**
     * ConditionErrorStack constructor.
     * @param $key
     * @param array $value
     * @param Validator $validator
     * @param bool $nullable
     */
    public function __construct($key, array $value, Validator $validator, bool $nullable = false)
    {
        $this->key = $key;
        $this->value = $value;
        $this->validator = $validator;
        $this->nullable = $nullable;
        $this->stackConditions($this->value);
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
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @return bool
     */
    public function isNullified(): bool
    {
        return ($this->nullable && empty($this->getValue()));
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
     * callback param 2: Condition[] | ConditionStack[] instances of siblings
     * @return ConditionErrorStack
     */
    public function validateChildren(Closure $callback): ConditionErrorStack {

        $conditions = $this->getConditionsFlat();
        foreach ($conditions as $condition) {
            call_user_func($callback, $condition['condition'], $condition['siblings']);
        }

        return $this;
    }

    /**
     * @param $index - also supports 'dot' notation
     * @param Closure $callback
     * callback param 1: Condition instance
     * callback param 2: Condition[] | ConditionStack[] instances of siblings
     * @return ConditionErrorStack
     */
    public function validateChild($index, Closure $callback): ConditionErrorStack {

        $condition = $this->getCondition($index);

        if (!is_array($condition)) throw new QueRuntimeException(
            "The index {$index} does not exist in condition stack", "Validation Error",
            E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        foreach ($condition as $con) {

            if (!(isset($con['condition']) && isset($con['siblings']))) throw new QueRuntimeException(
                "The index {$index} does not exist in condition stack", "Validation Error",
                E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

            call_user_func($callback, $con['condition'], $con['siblings']);

        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasError(): bool {
        return $this->isErrorInConditions();
    }

    /**
     * @param int $max
     * @param null $error
     * @return ConditionErrorStack
     */
    public function hasMaxSize($max = 3, $error = null): ConditionErrorStack
    {
        if (array_size($this->value) > $max) $this->addError($error);
        return $this;
    }

    /**
     * @param int $min
     * @param null $error
     * @return ConditionErrorStack
     */
    public function hasMinSize($min = 3, $error = null): ConditionErrorStack
    {
        if (array_size($this->value) < $min) $this->addError($error);
        return $this;
    }

    /**
     * @return array
     */
    public function getErrors() {
        return $this->getConditionErrors();
    }

    /**
     * @return array
     */
    public function getStatus() {
        return $this->getConditionStatuses();
    }

    /**
     * @param $error
     * @return ConditionErrorStack
     */
    public function addError($error): ConditionErrorStack
    {
        $this->addConditionError($error);
        return $this;
    }

    /**
     * @param $array
     */
    private function stackConditions($array) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->conditions[$key] = new ConditionErrorStack($key, $value, $this->validator, $this->nullable);
            } else {
                $this->conditions[$key] = new ConditionError($key, $value, $this->validator, $this->nullable);
            }
        }
    }

    /**
     * @return array
     */
    public function getConditionsFlat() {
        $list = [];
        foreach ($this->conditions as $condition) {
            if ($condition instanceof ConditionErrorStack) {
                $list = array_merge($list, $condition->getConditions());
            } elseif ($condition instanceof ConditionError) {
                $list[] = [
                    'condition' => $condition,
                    'siblings' => array_exclude($this->conditions, $condition->getKey())
                ];
            }
        }
        return $list;
    }

    /**
     * @return array
     */
    public function getConditions() {
        $list = [];
        foreach ($this->conditions as $condition) {
            if ($condition instanceof ConditionErrorStack) {
                $list[$condition->getKey()] = $condition->getConditions();
            } elseif ($condition instanceof ConditionError) {
                $list[$condition->getKey()] = [
                    'condition' => $condition,
                    'siblings' => array_exclude($this->conditions, $condition->getKey())
                ];
            }
        }
        return $list;
    }

    /**
     * @param $key
     * @return array|null
     */
    public function getCondition($key) {
        $conditions = $this->getConditions();
        $conditions = Arr::get($conditions, $key);
        return isset($conditions['condition']) && isset($conditions['siblings']) ? [$conditions] : $conditions;
    }

    /**
     * @return bool
     */
    private function isErrorInConditions() {
        foreach ($this->conditions as $condition)
            if ($condition->hasError()) return true;
        return false;
    }

    /**
     * @return array
     */
    private function getConditionErrors() {
        $errors = [];
        foreach ($this->conditions as $condition) {
            if ($condition instanceof ConditionErrorStack) {
                if ($condition->hasError()) $errors[$condition->getKey()] = $condition->getErrors();
            } elseif($condition instanceof ConditionError) {
                if ($condition->hasError()) $errors[$condition->getKey()] = $condition->getError();
            }
        }
        return $errors;
    }

    /**
     * @return array
     */
    private function getConditionStatuses() {

        $session = session()->getFiles();
        $statuses = [];

        foreach ($this->conditions as $condition) {

            if ($condition instanceof ConditionErrorStack) $statuses[$condition->getKey()] = $condition->getStatus();
            elseif ($condition instanceof ConditionError) {

                if ($condition->hasError()) {

                    if ($session->_isset("session.last-form-status.{$condition->getKey()}")) {

                        $statuses[$condition->getKey()] = WARNING;
                        $session->set("session.last-form-status.{$condition->getKey()}", true);

                    } else $statuses[$condition->getKey()] = ERROR;

                } else {

                    $statuses[$condition->getKey()] = SUCCESS;
                    $session->_unset("session.last-form-status.{$condition->getKey()}");

                }

            }

        }
        return $statuses;
    }

    /**
     * @param $error
     */
    private function addConditionError($error) {
        foreach ($this->conditions as $condition)
            $condition->setError($error);
    }

}
