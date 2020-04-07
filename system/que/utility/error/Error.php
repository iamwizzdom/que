<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/6/2020
 * Time: 4:18 PM
 */


namespace que\utility\error;

trait Error
{
    protected $errors = [];
    private static $instance;

    public static function instance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param $format
     * @param mixed ...$args
     */
    public function addErrorSprintF($format, ...$args): void
    {
        $this->errors[] = sprintf($format, ...$args);
    }

    /**
     * @param string $error
     */
    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * @param $key
     * @param string $arg
     */
    public function pushError($key, string $arg): void
    {
        $this->errors[$key] = $arg;
    }

    /**
     * @return int
     */
    public function countError(): int
    {
        return count($this->errors);
    }

    /**
     * @param $key
     * @return string|null
     */
    public function getError($key): ?string
    {
        return isset($this->errors[$key]) ? $this->errors[$key] : null;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return string
     */
    public function getErrorsString(): string
    {
        return implode(",", $this->errors);
    }

    /**
     * @param null $key
     * @return bool
     */
    public function hasError($key = null): bool
    {
        if ($key === null) return count($this->errors) > 0;
        return isset($this->errors[$key]);
    }

    /**
     * @return mixed
     */
    public function firstError()
    {
        return reset($this->errors);
    }

    /**
     * @return mixed
     */
    public function lastError()
    {
        return end($this->errors);
    }

    public function resetError(): void
    {
        $this->errors = [];
    }

    /**
     * @param array $errors
     */
    public function addBulkError(array $errors)
    {
        $this->errors = array_merge($this->errors, $errors);
    }

    /**
     * @param Error $error
     */
    public function mergeError(Error $error)
    {
        $this->addBulkError($error->getErrors());
    }
}