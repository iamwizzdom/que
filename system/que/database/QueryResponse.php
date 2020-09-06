<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 9/8/2018
 * Time: 10:14 PM
 */

namespace que\database;

use Closure;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\database\interfaces\drivers\DriverQueryBuilder;
use que\database\interfaces\drivers\DriverResponse;
use que\database\interfaces\model\Model;
use que\database\model\ModelStack;
use que\http\HTTP;

class QueryResponse
{

    /**
     * @var string
     */
    private string $table;

    /**
     * @var string
     */
    private string $primaryKey;

    /**
     * @var string|null
     */
    private ?string $model = null;

    /**
     * @var DriverResponse
     */
    private DriverResponse $driver_response;

    /**
     * @var int
     */
    private int $query_type;

    /**
     * QueryResponse constructor.
     * @param DriverResponse $response
     * @param int $query_type
     * @param string $table
     * @param string $primaryKey
     */
    public function __construct(DriverResponse $response, int $query_type, string $table, string $primaryKey)
    {
        $this->setDriverResponse($response);
        $this->setQueryType($query_type);
        $this->setTable($table);
        $this->setPrimaryKey($primaryKey);
        $this->setModel(config("database.default.model"));
    }

    /**
     * @return object|null
     */
    public function getFirst()
    {
        return $this->getQueryResponse(0);
    }

    /**
     * @param string $primaryKey
     * @return Model|null
     */
    public function getFirstWithModel(string $primaryKey = null)
    {
        return $this->getQueryResponseWithModel(0, $primaryKey);
    }

    /**
     * @return object[]|null
     */
    public function getAll()
    {
        $response = $this->getQueryResponse();
        return !is_array($response) ? [$response] : $response;
    }

    /**
     * @param string $primaryKey
     * @return ModelStack|null
     */
    public function getAllWithModel(string $primaryKey = null)
    {
        $response = $this->getQueryResponseWithModel(null, $primaryKey);
        if (empty($response)) return null;
        return $response instanceof ModelStack ? $response : new ModelStack([$response]);
    }

    /**
     * @param null $key
     * @return object[]|object|null
     */
    public function getQueryResponse($key = null)
    {
        $response = $this->getDriverResponse()->getResponse();

        if ($key !== null && is_array($response)) return isset($response[$key]) ? $this->normalize_data($response[$key]) : null;

        return $this->normalize_data($response);
    }

    /**
     * @param null $key
     * @return array|null
     */
    public function getQueryResponseArray($key = null)
    {
        $response = $this->getQueryResponse($key);

        if (empty($response)) return null;

        if ($key !== null) return (array) $response;

        if (is_object($response)) return (array) $response;

        array_callback($response, function ($row) {
            return (array) $row;
        });

        return (array) $response;
    }

    /**
     * @param null $key
     * @param string|null $primaryKey
     * @return Model|ModelStack|null
     */
    public function getQueryResponseWithModel($key = null, ?string $primaryKey = null)
    {
        if ($primaryKey === null) $primaryKey = $this->getPrimaryKey();

        $model = \model($this->getModel());

        if ($model === null) throw new QueRuntimeException(
            "No database model was found with the key '{$this->getModel()}', check your database configuration to fix this issue.",
            "Que Runtime Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        if (!($implements = class_implements($model)) || !isset($implements[Model::class])) throw new QueRuntimeException(
            "The specified model ({$model}) with key '{$this->getModel()}' does not implement the Que database model interface.",
            "Que Runtime Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        $response = $this->getQueryResponse($key);

        if (empty($response)) return null;

        if ($key !== null) {
            $response = (object) $response;
            return new $model($response, $this->getTable(), $primaryKey);
        }

        if (is_object($response)) {
            $response = (object) $response;
            return new $model($response, $this->getTable(), $primaryKey);
        }

        array_callback($response, function ($row) use ($model, $primaryKey) {
            $row = (object) $row;
            return new $model($row, $this->getTable(), $primaryKey);
        });

        return new ModelStack($response);
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->getDriverResponse()->isSuccessful() && (
            $this->getQueryType() === DriverQueryBuilder::SELECT ||
            $this->getQueryType() === DriverQueryBuilder::RAW_SELECT ?
                $this->getResponseSize() > 0 : (
            $this->getQueryType() === DriverQueryBuilder::UPDATE ||
            $this->getQueryType() === DriverQueryBuilder::DELETE ?
                $this->getAffectedRows() > 0 : (
            $this->getQueryType() === DriverQueryBuilder::CHECK ?
                $this->getQueryResponse() > 0 : (
            $this->getQueryType() === DriverQueryBuilder::RAW_OBJECT ?
                !empty($this->getQueryResponse()) : (
            $this->getQueryType() === DriverQueryBuilder::SUM ?
                !empty($this->getQueryResponse()) : true
            )))));
    }

    /**
     * @return int
     */
    public function getLastInsertID(): int
    {
        return $this->getDriverResponse()->getLastInsertID();
    }

    /**
     * @return int
     */
    public function getAffectedRows(): int
    {
        return $this->getDriverResponse()->getAffectedRows();
    }

    /**
     * @return string
     */
    public function getQueryError(): string
    {
        return serializer_recursive($this->getDriverResponse()->getErrors(), " -- ", function ($error) {
            return !empty($error);
        });
    }

    /**
     * @return string
     */
    public function getQueryErrorCode(): ?string
    {
        return $this->getDriverResponse()->getErrorCode();
    }

    /**
     * @return string
     */
    public function getQueryString(): string
    {
        return $this->getDriverResponse()->getQueryString();
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
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * @param string $primaryKey
     */
    public function setPrimaryKey(string $primaryKey): void
    {
        $this->primaryKey = $primaryKey;
    }

    /**
     * @return string|null
     */
    public function getModel(): ?string
    {
        return $this->model;
    }

    /**
     * @param string|null $model
     */
    public function setModel(?string $model): void
    {
        $this->model = $model;
    }


    /**
     * @return int
     */
    public function getResponseSize(): int
    {
        return is_array($res = $this->getQueryResponse()) ? array_size($res) : ($res !== null ? 1 : 0);
    }

    /**
     * @param Closure $callback
     * @return bool
     */
    public function query_response_walk(Closure $callback): bool
    {

        if (!$this->isSuccessful() || !is_callable($callback)) return false;

        $response = $this->getQueryResponse();

        if (!is_iterable($response)) return false;

        foreach ($response as $row) $callback($row);

        return true;
    }

    /**
     * @return DriverResponse
     */
    private function getDriverResponse(): DriverResponse
    {
        return $this->driver_response;
    }

    /**
     * @param DriverResponse $driver_response
     */
    private function setDriverResponse(DriverResponse $driver_response): void
    {
        $this->driver_response = $driver_response;
    }

    /**
     * @return int
     */
    public function getQueryType(): int
    {
        return $this->query_type;
    }

    /**
     * @param int $query_type
     */
    private function setQueryType(int $query_type): void
    {
        $this->query_type = $query_type;
    }

    /**
     * @param object|array $data
     * @return object|array
     */
    private function normalize_data($data)
    {
        if (is_iterable($data) || is_object($data)) {

            iterable_callback_recursive($data, function ($value) {
                if (is_iterable($value) || is_object($value)) return $this->normalize_data($value);
                return !is_null($value) ? ((json_decode($value) ?: $this->get_mark_down($value)) ?: stripslashes($value)) : null;
            });

        }

        return $data;
    }

    /**
     * @param $data
     * @return mixed|null
     */
    private function get_mark_down($data)
    {
        if (preg_match('/\[(.*?)\]\((.*?)\)\((.*?)\)/', $data, $matches)) {
            if ($matches[2] == "array" || $matches[2] == "object" || $matches[2] == "class") {

                if ($matches[2] == "class") {

                    if ($matches[3] === "true") return unserialize($matches[1]);
                    else return unserialize($matches[1], ['allowed_classes' => false]);

                } else return unserialize($matches[1]);
            }
        }
        return null;
    }

    /**
     * @param $data
     * @return string
     */
    private function getType($data)
    {
        return gettype($data);
    }

}
