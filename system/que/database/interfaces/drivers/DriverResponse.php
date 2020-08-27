<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 5/3/2020
 * Time: 9:38 PM
 */

namespace que\database\interfaces\drivers;


interface DriverResponse
{
    /**
     * @return bool
     */
    public function isSuccessful(): bool;

    /**
     * @return array|object|null
     */
    public function getResponse();

    /**
     * @param array|object|null $response
     */
    public function setResponse($response): void;

    /**
     * @return int
     */
    public function getLastInsertID(): int;

    /**
     * @return int
     */
    public function getAffectedRows(): int;

    /**
     * @return array
     */
    public function getErrors(): array;

    /**
     * @return string
     */
    public function getErrorCode(): string;

    /**
     * @return string
     */
    public function getQueryString(): string;
}