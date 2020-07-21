<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/20/2020
 * Time: 10:45 AM
 */

namespace que\database\interfaces\drivers;

interface Driver
{
    /**
     * @return DriverQueryBuilder
     */
    public function getQueryBuilder(): DriverQueryBuilder;

    /**
     * @param DriverQueryBuilder $builder
     * @return DriverResponse
     */
    public function exec(DriverQueryBuilder $builder): DriverResponse;

    /**
     * @return bool
     */
    public function reconnect(): bool;

    /**
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function changeUser(string $username, string $password): bool;

    /**
     * @param string $dbName
     * @return bool
     */
    public function changeDb(string $dbName): bool;

    /**
     * @param string $string
     * @return string
     */
    public function escape_string(string $string): string;

    /**
     * @return bool
     */
    public function beginTransaction(): bool;

    /**
     * @return bool
     */
    public function commit(): bool;

    /**
     * @return bool
     */
    public function rollback(): bool;

    /**
     * @return bool
     */
    public function close(): bool;

    /**
     * @return bool
     */
    public function isInDebugMode(): bool;
}