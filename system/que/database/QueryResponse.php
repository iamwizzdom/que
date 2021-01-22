<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 9/8/2018
 * Time: 10:14 PM
 */

namespace que\database;

use Closure;
use JetBrains\PhpStorm\Pure;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\database\interfaces\drivers\DriverQueryBuilder;
use que\database\interfaces\drivers\DriverResponse;
use que\database\interfaces\model\Model;
use que\database\model\ModelCollection;
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
    private ?string $modelKey = null;

    /**
     * @var string|null
     */
    private ?string $modelName = null;

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
        $this->setModelKey(config("database.default.model"));
    }

    /**
     * @return object
     */
    public function getFirst(): object
    {
        return $this->getQueryResponse(0);
    }

    /**
     * @return array
     */
    public function getFirstArray(): array
    {
        return $this->getQueryResponseArray(0);
    }

    /**
     * @param string|null $primaryKey
     * @return Model|null
     */
    public function getFirstWithModel(string $primaryKey = null): ?Model
    {
        try {

            return $this->getQueryResponseWithModel(0, $primaryKey);

        } catch (QueRuntimeException $e) {
            throw new QueRuntimeException($e->getMessage(), $e->getTitle(), $e->getCode(), $e->getHttpCode(),
                PreviousException::getInstance(2, $e->getPrevious()->getTrace()));
        }
    }

    /**
     * @return object|object[]
     */
    public function getAll(): object|array
    {
        return $this->getQueryResponse();
    }

    /**
     * @return array
     */
    public function getAllArray(): array
    {
        return $this->getQueryResponseArray();
    }

    /**
     * @param string|null $primaryKey
     * @return ModelCollection|null
     */
    public function getAllWithModel(string $primaryKey = null): ?ModelCollection
    {
        try {

            $response = $this->getQueryResponseWithModel(null, $primaryKey);
            if (empty($response)) return null;
            return $response instanceof ModelCollection ? $response : new ModelCollection([$response]);

        } catch (QueRuntimeException $e) {
            throw new QueRuntimeException($e->getMessage(), $e->getTitle(), $e->getCode(), $e->getHttpCode(),
                PreviousException::getInstance(2, $e->getPrevious()->getTrace()));
        }
    }

    /**
     * @param null $key
     * @return object[]|object|string
     */
    public function getQueryResponse($key = null): object|array|string
    {
        $response = $this->getDriverResponse()->getResponse();

        if ($key !== null && is_array($response)) return isset($response[$key]) ? $this->normalize_data($response[$key]) : (object)[];

        return $this->normalize_data($response);
    }

    /**
     * @param null $key
     * @return array
     */
    public function getQueryResponseArray($key = null): array
    {
        $response = $this->getQueryResponse($key);

        if (empty($response)) return [];

        if ($key !== null) return (array)$response;

        if (is_object($response)) return (array)$response;

        array_callback($response, function ($row) {
            return (array)$row;
        });

        return (array)$response;
    }

    /**
     * @param null $key
     * @param string|null $primaryKey
     * @return Model|ModelCollection|null
     */
    public function getQueryResponseWithModel($key = null, ?string $primaryKey = null): Model|ModelCollection|null
    {
        if ($primaryKey === null) $primaryKey = $this->getPrimaryKey();

        if (!$this->modelName) $this->setModelKey($this->getModelKey());

        $response = $this->getQueryResponse($key);

        if (empty($response)) return null;

        if ($key !== null) {
            $response = (object)$response;
            return new $this->modelName($response, $this->getTable(), $primaryKey);
        }

        if (is_object($response)) {
            $response = (object)$response;
            return new $this->modelName($response, $this->getTable(), $primaryKey);
        }

        array_callback($response, function ($row) use ($primaryKey) {
            $row = (object) $row;
            return new $this->modelName($row, $this->getTable(), $primaryKey);
        });

        return new ModelCollection($response);
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
        return implode(" | ", $this->getDriverResponse()->getErrors());
    }

    /**
     * @return string|null
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
    public function getModelKey(): ?string
    {
        return $this->modelKey;
    }

    /**
     * @param string $modelKey
     */
    public function setModelKey(string $modelKey): void
    {
        try {

            $modelName = \model($this->modelKey = $modelKey);
            $this->modelName = $this->verifyModel($modelName);

        } catch (QueRuntimeException $e) {
            throw new QueRuntimeException($e->getMessage(), $e->getTitle(), $e->getCode(), $e->getHttpCode(),
                PreviousException::getInstance(2, $e->getPrevious()->getTrace()));
        }
    }


    /**
     * @return int
     */
    public function getResponseSize(): int
    {
        return is_array($res = $this->getQueryResponse()) ? array_size($res) : (!empty($res) ? 1 : 0);
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
     * @param object|array|string $data
     * @return object|array|string
     */
    private function normalize_data(object|array|string $data): object|array|string
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
     * @return mixed
     */
    private function get_mark_down($data): mixed
    {
        if (preg_match('/\[(.*?)]\((.*?)\)\((.*?)\)/', $data, $matches)) {
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
     * @param string|null $modelName
     * @return string
     */
    private function verifyModel(?string $modelName): string
    {
        if ($modelName === null) throw new QueRuntimeException(
            "No database model was found with the key '{$this->getModelKey()}', check your database configuration to fix this issue.",
            "Que Runtime Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        if (!($implements = class_implements($modelName)) || !isset($implements[Model::class])) throw new QueRuntimeException(
            "The specified model ({$modelName}) with key '{$this->getModelKey()}' does not implement the Que database model interface.",
            "Que Runtime Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        return $modelName;
    }

    /**
     * @param $data
     * @return string
     */
    #[Pure] private function getType($data): string
    {
        return gettype($data);
    }

}
