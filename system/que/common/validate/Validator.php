<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 8/11/2018
 * Time: 9:24 PM
 */

namespace que\common\validate;

use que\common\exception\QueException;
use que\error\RuntimeError;
use que\http\input\Input;
use que\session\Session;

class Validator
{

    /**
     * @var array
     */
    private $errors = array();

    /**
     * @var Input
     */
    private $input = array();

    /**
     * @var array
     */
    private $condition = array();

    /**
     * @var Track
     */
    private static $track;

    /**
     * Validator constructor.
     * @param Input $input
     */
    public function __construct(Input $input)
    {
        $this->input = $input;
    }

    /**
     * @return Track
     */
    public function track(): Track {
        if (!self::$track instanceof Track)
            self::$track = Track::getInstance();
        return self::$track;
    }

    /**
     * @return Input
     */
    public function getInput(): Input
    {
        return $this->input;
    }

    /**
     * @param Input $input
     */
    public function setInput(Input $input) {
        $this->input = $input;
    }

    /**
     * @return array
     */
    public function getValues(): array {
        return $this->input->_get();
    }

    /**
     * @param null $key
     * @return mixed
     */
    public function getValue($key)
    {
        try {

            if (!array_key_exists($key, $this->input->_get()))
                throw new QueException("Undefined input key -- '{$key}'", "Validator error");

            return $this->input[$key];

        } catch (QueException $e) {

            RuntimeError::render(E_USER_ERROR, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(), $e->getTitle());
        }
    }

    /**
     * @param $key
     * @return int
     */
    public function getInt($key)
    {
        return (int) $this->getValue($key);
    }

    /**
     * @param $key
     * @return float
     */
    public function getFloat($key)
    {
        return (float) $this->getValue($key);
    }

    /**
     * @param $key
     * @param $value
     */
    public function setValue($key, $value) {
        $this->input[$key] = $value;
    }

    /**
     * @param $values
     * @param array $errors
     */
    public function updateValues($values, $errors = array()){
        $this->input = $values;
        array_merge($this->errors, $errors);
    }

    /**
     * @param $key
     * @return Condition
     */
    public function validate($key): Condition {

        try {

            if (!array_key_exists($key, $this->input->_get()))
                throw new QueException("Undefined input key -- '{$key}'", "Validator error");

            if (!isset($this->condition[$key]))
                $this->condition[$key] = new Condition($key, @$this->input[$key], $this);

            return $this->condition[$key];

        } catch (QueException $e) {

            RuntimeError::render(E_USER_ERROR, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(), $e->getTitle());
        }

    }

    /**
     * @param $key
     * @return ConditionStack
     */
    public function validateMulti($key): ConditionStack {

        try {

            if (!array_key_exists($key, $this->input->_get()))
                throw new QueException("Undefined input key -- '{$key}'", "Validator error");

            if (!is_array($this->input[$key]))
                throw new QueException("Value for input with key '{$key}' is not an array", "Validator error");

            if (!isset($this->condition[$key]))
                $this->condition[$key] = new ConditionStack($key, @$this->input[$key], $this);

            return $this->condition[$key];

        } catch (QueException $e) {

            RuntimeError::render(E_USER_ERROR, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(), $e->getTitle());
        }

    }

    /**
     * @return File
     */
    public function validateFile(): File {
        return File::getInstance($this->input);
    }

    /**
     * @return Base64Image
     */
    public function validateBase64Image(): Base64Image {
        return Base64Image::getInstance();
    }

    /**
     * @param $key
     * @param $value
     * @return Condition | ConditionStack
     */
    public function validateValue($key, $value) {
        if (is_array($value))
            $this->condition[$key] = new ConditionStack($key, $value, $this);
        else $this->condition[$key] = new Condition($key, $value, $this);
        return $this->condition[$key];
    }

    /**
     * @return bool
     */
    public function hasError(): bool {
        $hasError = false;
        foreach($this->condition as $condition) {
            if ($condition instanceof Condition) {
                if ($condition->hasError()) {
                    $hasError = true;
                    break;
                }
            } elseif ($condition instanceof ConditionStack) {
                if ($condition->hasError()) {
                    $hasError = true;
                    break;
                }
            }
        }
        return $hasError;
    }

    /**
     * @return int
     */
    public function totalError(): int {
        $count = 0;
        foreach($this->condition as $condition) {
            if ($condition instanceof Condition) {
                if ($condition->hasError()) $count++;
            } elseif ($condition instanceof ConditionStack)
                if ($condition->hasError()) $count++;
        }
        return $count;
    }

    /**
     * @param string $key
     * @param $variable
     * @return bool
     */
    public function isEqual(string $key, $variable): bool {
        return $this->input[$key] == $variable;
    }

    /**
     * @param string $key
     * @param $variable
     * @return bool
     */
    public function isIdentical(string $key, $variable): bool {
        return $this->input[$key] === $variable;
    }

    /**
     * @param $key
     * @return bool
     */
    public function isEmpty($key): bool {
        return empty($this->input[$key]) && $this->input[$key] != "0";
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool {
        return $this->input->_isset($key);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasFile(string $key): bool {
        if (!(isset($this->input[$key]) &&
            is_array($this->input[$key]))) return false;

        if (isset($this->input[$key]['size']) &&
            $this->input[$key]['size'] > 0) return true;

        foreach ($this->input[$key] as $value)
            if (isset($value['size']) && $value['size'] > 0) return true;

        return false;
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasConditionError($key): bool {

        if (!isset($this->condition[$key])) return false;

        if ($this->condition[$key] instanceof Condition)
            return $this->condition[$key]->hasError();
        elseif ($this->condition[$key] instanceof ConditionStack)
            return $this->condition[$key]->hasError();
    }

    /**
     * @return array
     */
    public function getErrors(): array {
        $errors = [];
        foreach($this->condition as $condition) {

            if ($condition instanceof Condition) {

                if ($condition->hasError())
                    $errors[$condition->getKey()] = $condition->getError();

            } elseif ($condition instanceof ConditionStack)
                $errors[$condition->getKey()] = $condition->getErrors();

        }
        return $errors;
    }

    /**
     * @return array
     */
    public function getErrorsFlat(): array {
        $errors = [];
        foreach($this->condition as $condition) {

            if ($condition instanceof Condition) {

                if ($condition->hasError())
                    $errors[$condition->getKey()] = current($condition->getError());

            } elseif ($condition instanceof ConditionStack)
                $errors[$condition->getKey()] = current($condition->getError()) ?: $condition->getErrorsFlat();
        }
        return $errors;
    }

    /**
     * @return array
     */
    public function getStatus(): array {
        $status = []; $session = &Session::getInstance()->getFiles()->_get();
        foreach($this->condition as $condition) {

            if ($condition instanceof Condition) {

                if ($condition->hasError()) {

                    if (!isset($session['session']['last-form-status'][$condition->getKey()])) {
                        $status[$condition->getKey()] = WARNING;
                        $session['session']['last-form-status'][$condition->getKey()] = true;
                    } else $status[$condition->getKey()] = ERROR;

                } else {
                    $status[$condition->getKey()] = SUCCESS;
                    if (isset($session['session']['last-form-status'][$condition->getKey()]))
                        unset($session['session']['last-form-status'][$condition->getKey()]);
                }

            } elseif ($condition instanceof ConditionStack)
                $status[$condition->getKey()] = $condition->getStatus();

        }
        return $status;
    }

    /**
     * @param $key
     * @param $error
     * @return Condition | ConditionStack
     */
    public function addError($key, $error) {
        if (!isset($this->condition[$key])) {
            if (is_array($this->input[$key])) {
                $this->condition[$key] = new ConditionStack($key, $this->input[$key], $this);
            } else $this->condition[$key] = new Condition($key, $this->input[$key], $this);
        }
        $this->condition[$key]->addError($error);
        return $this->condition[$key];
    }

    /**
     * @param $key
     * @param array $errors
     * @return Condition | ConditionStack
     */
    public function addErrors($key, array $errors) {
        if (!isset($this->condition[$key])) {
            if (is_array($this->input[$key])) {
                $this->condition[$key] = new ConditionStack($key, $this->input[$key], $this);
            } else $this->condition[$key] = new Condition($key, $this->input[$key], $this);
        }
        foreach($errors as $error) $this->condition[$key]->addError($error);
        return $this->condition[$key];
    }

    /**
     * @param $key
     * @param Condition $condition
     * @return Condition
     */
    public function addCondition($key, Condition $condition): Condition {
        $this->condition[$key] = $condition;
        return $this->condition[$key];
    }

    /**
     * @param $key
     * @param ConditionStack $condition
     * @return ConditionStack
     */
    public function addConditionStack($key, ConditionStack $condition): ConditionStack {
        $this->condition[$key] = $condition;
        return $this->condition[$key];
    }

    /**
     *
     * @param string $key
     * @return Condition | ConditionStack
     */
    public function getCondition($key) {
        if (!isset($this->condition[$key])) {

            if (is_array($this->input[$key]))
                $this->condition[$key] = new ConditionStack($key, $this->input[$key], $this);
            else $this->condition[$key] = new Condition($key, $this->input[$key], $this);
        }

        return $this->condition[$key];
    }

    /**
     * @return array
     */
    public function getConditionFlat(): array {
        return $this->condition;
    }

    /**
     * @param $key
     * @param string $algo
     * @return $this
     */
    public function hash($key, string $algo): Validator {
        $hash = hash($algo, $this->getValue($key));
        $this->setValue($key, $hash);
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function trim($key): Validator {
        $value = trim($this->getValue($key));
        $this->setValue($key, $value);
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function toUpper($key): Validator {
        $value = strtoupper($this->getValue($key));
        $this->setValue($key, $value);
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function toLower($key): Validator {
        $value = strtolower($this->getValue($key));
        $this->setValue($key, $value);
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function toUcFirst($key): Validator {
        $value = ucfirst($this->getValue($key));
        $this->setValue($key, $value);
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function toUcWords($key): Validator {
        $value = ucwords($this->getValue($key));
        $this->setValue($key, $value);
        return $this;
    }

    /**
     * @param $key
     * @param $function
     * @return Validator
     */
    public function _call($key, $function): Validator {
        if (!function_exists($function)) return $this;
        $value = call_user_func($function, $this->getValue($key));
        $this->setValue($key, $value);
        return $this;
    }

}