<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 9/8/2018
 * Time: 10:14 PM
 */

namespace que\database\mysql;

use Closure;
use que\model\Model;

class MySQL_Handler
{

    /**
     * @var string
     */
    private $table;

    /**
     * @var null
     */
    private $query_response = null;

    /**
     * @var bool
     */
    private $successful = false;

    /**
     * @var string
     */
    private $query_error = "";

    /**
     * @var int
     */
    private $query_error_code = 0;

    /**
     * @var int
     */
    private $response_size = 0;

    /**
     * @var string
     */
    private $query_string = "";

    /**
     * MySQL_Handler constructor.
     * @param $query_response
     * @param bool $query_status
     * @param string $query_error
     * @param int $query_error_code
     * @param string $query_string
     * @param string $table
     */
    public function __construct($query_response, bool $query_status,
                                string $query_error, ?int $query_error_code, string $query_string, string $table)
    {
        $this->setTable($table);
        $this->setSuccessful($query_status);
        $this->setQueryResponse($query_response);
        $this->setResponseSize($query_response);
        $this->setQueryError($query_error);
        $this->setQueryErrorCode($query_error_code);
        $this->setQueryString($query_string);
    }

    /**
     * @param null $key
     * @return mixed|null
     */
    public function getQueryResponse($key = null)
    {
        return (!is_null($key) && isset($this->query_response[$key]) ?
            $this->query_response[$key] : $this->query_response);
    }

    /**
     * @param null $key
     * @return array
     */
    public function getQueryResponseArray($key = null)
    {
        if (empty($this->query_response))
            return $this->query_response;

        if (!is_null($key) && isset($this->query_response[$key]))
            return (array) $this->query_response[$key];

        $queue = [];

        foreach ($this->query_response as $_key => $row)
            $queue[$_key] = (array) $row;

        return $queue;
    }

    /**
     * @param null $key
     * @return array|Model
     */
    public function getQueryResponseWithModel($key = null)
    {
        if (empty($this->query_response))
            return $this->query_response;

        if (!is_null($key) && isset($this->query_response[$key]))
            return new Model($this->query_response[$key], $this->getTable());

        $queue = [];

        foreach ($this->query_response as $_key => &$row)
            $queue[$_key] = new Model($row, $this->getTable());

        return $queue;
    }

    /**
     * @param $query_response
     */
    private function setQueryResponse($query_response)
    {
        $this->query_response = $query_response;
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    /**
     * @param bool $successful
     */
    private function setSuccessful(bool $successful)
    {
        $this->successful = $successful;
    }

    /**
     * @return string
     */
    public function getQueryError(): string
    {
        return "MySQL Error: {$this->query_error}";
    }

    /**
     * @param string $query_error
     */
    private function setQueryError(string $query_error)
    {
        $this->query_error = $query_error;
    }

    /**
     * @return int
     */
    public function getQueryErrorCode(): ?int
    {
        return $this->query_error_code;
    }

    /**
     * @param int $query_error_code
     */
    private function setQueryErrorCode(?int $query_error_code): void
    {
        $this->query_error_code = $query_error_code ?? 0;
    }

    /**
     * @return string
     */
    public function getQueryString(): string
    {
        return "SQL: {$this->query_string}";
    }

    /**
     * @param string $query_string
     */
    private function setQueryString(string $query_string)
    {
        $this->query_string = $query_string;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $table
     */
    private function setTable(string $table): void
    {
        $this->table = $table;
    }


    /**
     * @return int
     */
    public function getResponseSize(): int
    {
        return $this->response_size;
    }

    /**
     * @param $response
     */
    private function setResponseSize($response)
    {
        $this->response_size = (is_array($response) ?  count($response) : 0);
    }

    /**
     * @param Closure $callback
     * @return bool
     */
    public function query_response_walk(Closure $callback): bool {
        if (!$this->isSuccessful() || !is_callable($callback)) return false;
        $response = $this->getQueryResponse();
        foreach ($response as $row) $callback($row);
        return true;
    }

    /**
     * @param Closure $callback
     * @return bool
     */
    public function query_response_walk_with_model(Closure $callback): bool {
        if (!$this->isSuccessful() || !is_callable($callback)) return false;
        $response = $this->getQueryResponse();
        foreach ($response as $row) $callback(new Model($row, $this->getTable()));
        return true;
    }

}