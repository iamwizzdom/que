<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 5/3/2020
 * Time: 10:33 PM
 */

namespace que\database\drivers\mysql;


use que\database\interfaces\drivers\DriverResponse;

class MySqlDriverResponse implements DriverResponse
{

    /**
     * @var array|null
     */
    private $response;

    /**
     * @var bool
     */
    private bool $successful;

    /**
     * @var string
     */
    private string $query;

    /**
     * @var array
     */
    private array $error;

    /**
     * @var string
     */
    private string $errorCode;

    /**
     * @var int
     */
    private int $lastInsertID;

    /**
     * @var int
     */
    private int $affectedRows;

    /**
     * MySqlDriverResponse constructor.
     * @param $response
     * @param bool $successful
     * @param string $query
     * @param array $error
     * @param string $errorCode
     * @param int $lastInsertID
     * @param int $affectedRows
     */
    public function __construct(
        $response, bool $successful, string $query, array $error,
        string $errorCode, int $lastInsertID = 0, int $affectedRows = 0
    )
    {
        $this->response = $response;
        $this->successful = $successful;
        $this->query = $query;
        $this->error = $error;
        $this->errorCode = $errorCode;
        $this->lastInsertID = $lastInsertID;
        $this->affectedRows = $affectedRows;
    }

    /**
     * @inheritDoc
     */
    public function isSuccessful(): bool
    {
        // TODO: Implement isSuccessful() method.
        return $this->successful;
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): array
    {
        // TODO: Implement getError() method.
        return $this->error;
    }

    /**
     * @inheritDoc
     */
    public function getErrorCode(): string
    {
        // TODO: Implement getErrorCode() method.
        return $this->errorCode;
    }

    /**
     * @inheritDoc
     */
    public function getQueryString(): string
    {
        // TODO: Implement getQueryString() method.
        return $this->query;
    }

    /**
     * @inheritDoc
     */
    public function getResponse()
    {
        // TODO: Implement getResponse() method.
        return $this->response;
    }

    /**
     * @inheritDoc
     */
    public function getLastInsertID(): int
    {
        // TODO: Implement getLastID() method.
        return $this->lastInsertID;
    }

    /**
     * @inheritDoc
     */
    public function getAffectedRows(): int
    {
        // TODO: Implement getAffectedRows() method.
        return $this->affectedRows;
    }
}