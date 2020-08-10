<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 8/11/2018
 * Time: 9:24 PM
 */

namespace que\common\validator;

use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\common\validator\condition\ConditionError;
use que\common\validator\condition\ConditionErrorStack;
use que\http\HTTP;
use que\http\input\Input;
use que\session\Session;
use que\utility\hash\Hash;

class Validator
{

    /**
     * @var array
     */
    private array $errors = [];

    /**
     * @var Input
     */
    private $input = [];

    /**
     * @var ConditionError[]|ConditionErrorStack[]
     */
    private array $conditions = [];

    /**
     * @var Track
     */
    private static ?Track $track = null;

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
     * @param $key
     * @return mixed|null
     */
    public function getValue($key)
    {
        return $this->input[$key] ?? null;
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
    public function updateValues($values, $errors = []){
        $this->input = $values;
        array_merge($this->errors, $errors);
    }

    /**
     * @param $key
     * @param bool $nullable
     * @return ConditionError
     */
    public function validate($key, bool $nullable = false): ConditionError {

        if (is_array($this->input[$key] ?? null))
            throw new QueRuntimeException("Value for input with key '{$key}' is of type array, use the [validateMulti] method instead",
                "Validator error", 0, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        if (!isset($this->conditions[$key]))
            $this->conditions[$key] = new ConditionError($key, $this->input[$key] ?? null, $this, $nullable);

        return $this->conditions[$key];

    }

    /**
     * @param $key
     * @param bool $nullable
     * @return ConditionErrorStack
     */
    public function validateMulti($key, bool $nullable = false): ConditionErrorStack {

        if (!is_array($this->input[$key] ?? []))
            throw new QueRuntimeException("Value for input with key '{$key}' is not of type array, use the [validate] method instead",
                "Validator error", 0, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        if (!isset($this->conditions[$key]))
            $this->conditions[$key] = new ConditionErrorStack($key, $this->input[$key] ?? [], $this, $nullable);

        return $this->conditions[$key];

    }

    /**
     * @return File
     */
    public function validateFile(): File {
        return File::getInstance($this->input->getFiles());
    }

    /**
     * @return Base64File
     */
    public function validateBase64File(): Base64File {
        return Base64File::getInstance();
    }

    /**
     * @param $key
     * @param $value
     * @return ConditionError
     */
    public function validateValue($key, $value) {

        if (is_array($value))
            throw new QueRuntimeException("Value must not be of type array, or use the [validateMultiValue] method instead",
                "Validator error", 0, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        return $this->conditions[$key] = new ConditionError($key, $value, $this);
    }

    /**
     * @param $key
     * @param $value
     * @return ConditionErrorStack
     */
    public function validateMultiValue($key, $value) {

        if (!is_array($value))
            throw new QueRuntimeException("Value must be of type array, or use the [validateValue] method instead",
                "Validator error", 0, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        return $this->conditions[$key] = new ConditionErrorStack($key, $value, $this);
    }

    /**
     * @return bool
     */
    public function hasError(): bool {
        $hasError = false;
        foreach($this->conditions as $condition) {
            if ($condition->hasError()) {
                $hasError = true;
                break;
            }
        }
        return $hasError;
    }

    /**
     * @return int
     */
    public function totalError(): int {
        $count = 0;
        foreach($this->conditions as $condition) {
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

        $files = $this->input->getFiles();

        if (!(isset($files[$key]) && is_array($files[$key]))) return false;

        if (isset($files[$key]['size']) && $files[$key]['size'] > 0) return true;

        if (is_array($files[$key])) {

            foreach ($files[$key] as $value)
                if (isset($value['size']) && $value['size'] > 0) return true;
        }

        return false;
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasConditionError($key): bool {

        if (!isset($this->conditions[$key])) return false;

        return $this->conditions[$key]->hasError();
    }

    /**
     * @param $key
     * @return array
     */
    public function getError($key): array {
        $errors = [];
        $condition = ($this->conditions[$key] ?? null);
        if ($condition instanceof ConditionError) {

            if ($condition->hasError())
                $errors[$condition->getKey()] = $condition->getError();

        } elseif ($condition instanceof ConditionErrorStack)
            $errors[$condition->getKey()] = $condition->getErrors();

        return $errors;
    }

    /**
     * @return array
     */
    public function getErrors(): array {
        $errors = [];
        foreach($this->conditions as $condition) {

            if ($condition instanceof ConditionError) {

                if ($condition->hasError())
                    $errors[$condition->getKey()] = $condition->getError();

            } elseif ($condition instanceof ConditionErrorStack)
                $errors[$condition->getKey()] = $condition->getErrors();

        }
        return $errors;
    }

    /**
     * @param $key
     * @return array
     */
    public function getErrorFlat($key): array {
        $errors = [];
        $condition = ($this->conditions[$key] ?? null);
        if ($condition instanceof ConditionError) {

            if ($condition->hasError())
                $errors[$condition->getKey()] = current($condition->getError());

        } elseif ($condition instanceof ConditionErrorStack)
            $errors[$condition->getKey()] = current($condition->getErrorsFlat());

        return $errors;
    }

    /**
     * @return array
     */
    public function getErrorsFlat(): array {
        $errors = [];
        foreach($this->conditions as $condition) {

            if ($condition instanceof ConditionError) {

                if ($condition->hasError())
                    $errors[$condition->getKey()] = current($condition->getError());

            } elseif ($condition instanceof ConditionErrorStack)
                $errors[$condition->getKey()] = $condition->getErrorsFlat();
        }
        return $errors;
    }

    /**
     * @param $key
     * @return array
     */
    public function getStatus($key): array {
        $status = []; $session = \session()->getFiles();
        $condition = ($this->conditions[$key] ?? null);
        if ($condition instanceof ConditionError) {

            if ($condition->hasError()) {

                if ($session->_isset("session.last-form-status.{$condition->getKey()}")) {
                    $status[$condition->getKey()] = WARNING;
                    $session->set("session.last-form-status.{$condition->getKey()}", true);
                } else $status[$condition->getKey()] = ERROR;

            } else {
                $status[$condition->getKey()] = SUCCESS;
                $session->_unset("session.last-form-status.{$condition->getKey()}");
            }

        } elseif ($condition instanceof ConditionErrorStack)
            $status[$condition->getKey()] = $condition->getStatus();

        return $status;
    }

    /**
     * @return array
     */
    public function getStatuses(): array {
        $status = [];
        foreach($this->conditions as $condition) {
            $status = array_merge_recursive($status, $this->getStatus($condition->getKey()));
        }
        return $status;
    }

    /**
     * @param $key
     * @param $error
     * @return ConditionError|ConditionErrorStack
     */
    public function addError($key, $error) {
        if (!isset($this->conditions[$key])) {
            if (is_array($this->input[$key])) {
                $this->conditions[$key] = new ConditionErrorStack($key, $this->input[$key], $this);
            } else $this->conditions[$key] = new ConditionError($key, $this->input[$key], $this);
        }
        $this->conditions[$key]->addError($error);
        return $this->conditions[$key];
    }

    /**
     * @param $key
     * @param array $errors
     * @return ConditionError|ConditionErrorStack
     */
    public function addErrors($key, array $errors) {
        if (!isset($this->conditions[$key])) {
            if (is_array($this->input[$key])) {
                $this->conditions[$key] = new ConditionErrorStack($key, $this->input[$key], $this);
            } else $this->conditions[$key] = new ConditionError($key, $this->input[$key], $this);
        }
        foreach($errors as $error) $this->conditions[$key]->addError($error);
        return $this->conditions[$key];
    }

    /**
     * @param $key
     * @param $error
     * @param bool $force_add - Setting this to [bool](true) might result in some unexpected errors
     * @return ConditionError
     */
    public function addConditionError($key, $error, bool $force_add = false) {

        if (is_array($this->input[$key]) && !$force_add)
            throw new QueRuntimeException("Value for input with key '{$key}' is of type array, use the [addConditionStackError] method instead",
                "Validator error", 0, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        if (!isset($this->conditions[$key]))
            $this->conditions[$key] = new ConditionError($key, $this->input[$key], $this);

        $this->conditions[$key]->addError($error);
        return $this->conditions[$key];
    }

    /**
     * @param $key
     * @param array $errors
     * @param bool $force_add - Setting this to [bool](true) might result in some unexpected errors
     * @return ConditionError
     */
    public function addConditionErrors($key, array $errors, bool $force_add = false) {

        if (is_array($this->input[$key]) && !$force_add)
            throw new QueRuntimeException("Value for input with key '{$key}' is of type array, use the [addConditionStackErrors] method instead",
                "Validator error", 0, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        if (!isset($this->conditions[$key]))
            $this->conditions[$key] = new ConditionError($key, $this->input[$key], $this);

        foreach($errors as $error) $this->conditions[$key]->addError($error);
        return $this->conditions[$key];
    }

    /**
     * @param $key
     * @param $error
     * @param bool $force_add - Setting this to [bool](true) might result in some unexpected errors
     * @return ConditionErrorStack
     */
    public function addConditionStackError($key, $error, bool $force_add = false) {

        if (!is_array($this->input[$key]) && !$force_add)
            throw new QueRuntimeException("Value for input with key '{$key}' is not of type array, use the [addConditionError] method instead",
                "Validator error", 0, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        if (!isset($this->conditions[$key]))
            $this->conditions[$key] = new ConditionErrorStack($key, $this->input[$key], $this);

        $this->conditions[$key]->addError($error);
        return $this->conditions[$key];
    }

    /**
     * @param $key
     * @param array $errors
     * @param bool $force_add - Setting this to [bool](true) might result in some unexpected errors
     * @return ConditionErrorStack
     */
    public function addConditionStackErrors($key, array $errors, bool $force_add = false) {

        if (!is_array($this->input[$key]) && !$force_add)
            throw new QueRuntimeException("Value for input with key '{$key}' is not of type array, use the [addConditionErrors] method instead",
                "Validator error", 0, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        if (!isset($this->conditions[$key]))
            $this->conditions[$key] = new ConditionErrorStack($key, $this->input[$key], $this);

        foreach($errors as $error) $this->conditions[$key]->addError($error);
        return $this->conditions[$key];
    }

    /**
     * @param $key
     * @param ConditionError $condition
     * @return ConditionError
     */
    public function addCondition($key, ConditionError $condition): ConditionError {
        $this->conditions[$key] = $condition;
        return $this->conditions[$key];
    }

    /**
     * @param $key
     * @param ConditionErrorStack $condition
     * @return ConditionErrorStack
     */
    public function addConditionStack($key, ConditionErrorStack $condition): ConditionErrorStack {
        $this->conditions[$key] = $condition;
        return $this->conditions[$key];
    }

    /**
     * @param $key
     * @param null $default [Default condition]
     * @return ConditionError|ConditionErrorStack
     */
    public function getConditions($key, $default = null) {
        return $this->conditions[$key] ?? $default;
    }

    /**
     * @return array
     */
    public function getConditionFlat(): array {
        return $this->conditions;
    }

    /**
     * @param $key
     * @param string $algo
     * @return $this
     */
    public function hash($key, string $algo = "SHA256"): Validator {
        $this->setValue($key, Hash::sha($this->getValue($key), $algo));
        return $this;
    }

    /**
     * @param $key
     * @param string $charlist
     * @return $this
     */
    public function trim($key, string $charlist = " \t\n\r\0\x0B"): Validator {
        $this->setValue($key, trim($this->getValue($key), $charlist));
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function toUpper($key): Validator {
        $this->setValue($key, strtoupper($this->getValue($key)));
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function toLower($key): Validator {
        $this->setValue($key, strtolower($this->getValue($key)));
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function toUcFirst($key): Validator {
        $this->setValue($key, ucfirst($this->getValue($key)));
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function toUcWords($key): Validator {
        $this->setValue($key, ucwords($this->getValue($key)));
        return $this;
    }

    /**
     * @param $key
     * @param $function
     * @param array $parameter
     * @return Validator
     */
    public function _call($key, $function, ...$parameter): Validator {
        if (!function_exists($function)) return $this;
        array_unshift($parameter, $this->getValue($key));
        $this->setValue($key, call_user_func($function, ...$parameter));
        return $this;
    }

}
