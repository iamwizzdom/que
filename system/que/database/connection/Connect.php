<?php

namespace que\database\connection;

use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\database\interfaces\drivers\Driver;
use que\support\Arr;
use que\support\Config;

abstract class Connect
{
    /**
     * @var string
     */
    private string $driver;

    /**
     * @var array
     */
    private array $drivers = [];

    /**
     * @var bool
     */
    private bool $transEnabled = true;

    /**
     * @var bool
     */
    private bool $transStrict = true;

    /**
     * @var bool
     */
    private bool $transSuccessful = true;

    /**
     * @var bool
     */
    private bool $transFailed = false;

    /**
     * @var int
     */
    protected int $transDepth = 0;

    /**
     * @return Driver
     */
    protected function getDriver(): Driver {

        if (!isset($this->drivers[$this->driver])) {

            $driver = Arr::get(Config::get("database.drivers", []), $this->driver);

            if (is_null($driver)) throw new QueRuntimeException(
                "Invalid Driver: No database driver exists with the key '{$this->driver}', check your database configuration to fix this issue.",
                "Database Error", E_USER_ERROR, 0, PreviousException::getInstance());

            if (!class_exists($driver)) throw new QueRuntimeException(
                "Invalid Driver: Database driver with the key '{$this->driver}' is not a class, check your database configuration to fix this issue.",
                "Database Error", E_USER_ERROR, 0, PreviousException::getInstance());

            $driver = new $driver;

            if (!$driver instanceof Driver) throw new QueRuntimeException(
                "Invalid Driver: Database driver '{$this->getDriverName($driver)}' with key '{$this->driver}' does not implement the system database driver interface.",
                "Database Error", E_USER_ERROR, 0, PreviousException::getInstance());

            $this->drivers[$this->driver] = $driver;
        }

        return $this->drivers[$this->driver];
    }

    /**
     * @param $class
     * @return string
     */
    private function getDriverName($class)
    {
        return get_class($class);
    }

    /**
     * @param string $driver
     */
    public function changeDriver(string $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function changeUser(string $username, string $password): bool
    {
        return $this->getDriver()->changeUser($username, $password);
    }

    /**
     * @param string $dbName
     * @return bool
     */
    public function changeDb(string $dbName): bool
    {
        return $this->getDriver()->changeDb($dbName);
    }

    /**
     * @return bool
     */
    public function reconnect(): bool
    {
        return $this->getDriver()->reconnect();
    }

    /**
     * @param string $string
     * @return string
     */
    public function escape_string(string $string): string
    {
        return $this->getDriver()->escape_string($string);
    }

    /**
     * @return int
     */
    protected function getTransDepth(): int
    {
        return $this->transDepth;
    }

    /**
     * @return bool
     */
    public function isTransEnabled(): bool
    {
        return $this->transEnabled;
    }

    /**
     * @param bool $transEnabled
     */
    public function setTransEnabled(bool $transEnabled): void
    {
        $this->transEnabled = $transEnabled;
    }

    /**
     * @return bool
     */
    public function isTransStrict(): bool
    {
        return $this->transStrict;
    }

    /**
     * @param bool $transStrict
     */
    protected function setTransStrict(bool $transStrict): void
    {
        $this->transStrict = $transStrict;
    }

    /**
     * @return bool
     */
    public function isTransSuccessful(): bool
    {
        return $this->transSuccessful;
    }

    /**
     * @param bool $transSuccessful
     */
    protected function setTransSuccessful(bool $transSuccessful): void
    {
        $this->transSuccessful = $transSuccessful;
    }

    /**
     * @return bool
     */
    public function isTransFailed(): bool
    {
        return $this->transFailed;
    }

    /**
     * @param bool $transFailed
     */
    protected function setTransFailed(bool $transFailed): void
    {
        $this->transFailed = $transFailed;
    }

    /**
     * @param string|null $driver
     * @return bool
     */
    public function close(string $driver = null): bool
    {
        if ($driver === null) $driver = $this->driver;
        $driver = $this->drivers[$key = $driver] ?? null;
        if (!$driver instanceof Driver) return false;
        unset($this->drivers[$key]);
        return $driver->close();
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        foreach ($this->drivers as $key => $driver) $this->close($key);
    }

}