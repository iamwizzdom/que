<?php


namespace que\common\validate;


use Closure;

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
     * callback param 2: Condition instance of siblings
     * @return ConditionStack
     */
    public function validateChildren(Closure $callback): ConditionStack {

        $conditions = $this->getConditions();

        foreach ($conditions as &$condition) {
            $con = &$condition['condition'];
            call_user_func($callback, $con, $condition['siblings']);
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
     * @return ConditionStack
     */
    public function hasMaxSize($max = 3, $error = null): ConditionStack
    {
        if (array_size($this->value) > $max) $this->addError($error);
        return $this;
    }

    /**
     * @param int $min
     * @param null $error
     * @return ConditionStack
     */
    public function hasMinSize($min = 3, $error = null): ConditionStack
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
    public function getErrorsFlat() {
        return $this->getConditionErrorsFlat();
    }

    /**
     * @param $error
     * @return ConditionStack
     */
    public function addError($error): ConditionStack
    {
        $this->addConditionError($error);
        return $this;
    }

    /**
     * @param $session
     * @return mixed
     */
    public function getStatus(&$session) {
        return $this->getConditionStatuses($session);
    }

    /**
     * @param $values
     */
    private function stackConditions($values) {
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $this->conditions[$key] = new ConditionStack($key, $value, $this->validator);
            } else {
                $this->conditions[$key] = new Condition($key, $value, $this->validator);
            }
        }
    }

    /**
     * @return array
     */
    public function getConditions() {
        $list = [];
        foreach ($this->conditions as &$condition) {
            if ($condition instanceof ConditionStack) {
                $list = array_merge($list, $condition->getConditions());
            } elseif ($condition instanceof Condition) {
                $list[] = [
                    'condition' => &$condition,
                    'siblings' => array_exclude($this->conditions, [$condition->getKey()])
                ];
            }
        }
        return $list;
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
            if ($condition instanceof ConditionStack) $errors[$condition->getKey()] = $condition->getErrors();
            elseif($condition instanceof Condition) $errors[$condition->getKey()] = $condition->getError();
        }
        return $errors;
    }

    /**
     * @return array
     */
    private function getConditionErrorsFlat() {
        $errors = [];
        foreach ($this->conditions as $condition) {
            if ($condition instanceof ConditionStack) $errors[$condition->getKey()] = $condition->getErrorsFlat();
            elseif ($condition instanceof Condition) $errors[$condition->getKey()] = current($condition->getError());
        }
        return $errors;
    }

    /**
     * @param $session
     * @return array
     */
    private function getConditionStatuses(&$session) {

        $statuses = [];

        foreach ($this->conditions as $condition) {

            if ($condition instanceof ConditionStack) $statuses[$condition->getKey()] = $condition->getStatus($session);
            elseif ($condition instanceof Condition) {

                if ($condition->hasError()) {

                    if (!isset($session[$condition->getKey()])) {

                        $statuses[$condition->getKey()] = WARNING;
                        $session[$condition->getKey()] = true;

                    } else $statuses[$condition->getKey()] = ERROR;

                } else {

                    $statuses[$condition->getKey()] = SUCCESS;
                    if (isset($session[$condition->getKey()])) unset($session[$condition->getKey()]);

                }

            }

        }
        return $statuses;
    }

    /**
     * @param $error
     */
    private function addConditionError($error) {
        foreach ($this->conditions as &$condition)
            $condition->addError($error);
    }

}