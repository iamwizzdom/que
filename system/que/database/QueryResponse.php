<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 9/8/2018
 * Time: 10:14 PM
 */

namespace que\database\mysql;

use Closure;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\database\interfaces\DriverQueryBuilder;
use que\database\interfaces\DriverResponse;
use que\database\model\interfaces\Model;
use que\database\model\ModelStack;

class QueryResponse
{

    /**
     * @var string
     */
    private string $table;

    /**
     * @var DriverResponse
     */
    private DriverResponse $driver_response;

    /**
     * @var int
     */
    private int $query_type;

    /**
     * MySQL_Handler constructor.
     * @param DriverResponse $response
     * @param int $query_type
     * @param string $table
     */
    public function __construct(DriverResponse $response, int $query_type, string $table)
    {
        if (str_contains($table, " ")) $table = str_strip_spaces(str_end_at($table, " "));
        $this->setDriverResponse($response);
        $this->setQueryType($query_type);
        $this->setTable($table);
    }

    /**
     * @param null $key
     * @return array|object|null
     */
    public function getQueryResponse($key = null)
    {
        $response = $this->getDriverResponse()->getResponse();

        if (!is_null($key)) {

            if (is_array($response)) {

                if (!isset($response[$key])) return null;

                return $this->normalize_data($response[$key]);
            }

            if (is_object($response)) {

                if (!object_key_exists($key, $response)) return null;

                return $this->normalize_data($response->{$key});
            }

            return null;
        }

        return is_iterable($response) ? $this->normalize_data($response) : $response;
    }

    /**
     * @param null $key
     * @return array
     */
    public function getQueryResponseArray($key = null)
    {
        $response = $this->getQueryResponse($key);

        if (empty($response)) return (array) $response;

        if (!is_null($key)) return (array) $response;

        if (!is_array($response)) $response = (array) $response;

        array_callback($response, function ($row) {
            return (array) $this->normalize_data($row);
        });

        return $response;
    }

    /**
     * @param string|null $model
     * @param null $key
     * @param string $primaryKey
     * @return Model|ModelStack|null
     */
    public function getQueryResponseWithModel(string $model = null, $key = null,string $primaryKey = "id")
    {
        $model = \model(($modelKey = $model ?? config("database.default.model")));

        if ($model === null) throw new QueRuntimeException(
            "No database model was found with the key '{$modelKey}', check your database configuration to fix this issue.",
            "Que Runtime Error", E_USER_ERROR, HTTP_INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        if (!($implements = class_implements($model)) || !in_array(Model::class, $implements)) throw new QueRuntimeException(
            "The specified model ({$model}) with key '{$modelKey}' does not implement the Que database model interface.",
            "Que Runtime Error", E_USER_ERROR, HTTP_INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        $response = $this->getQueryResponse($key);

        if (empty($response)) return null;

        if (!is_null($key)) {

            if (!is_object($response)) throw new QueRuntimeException(
                self::class . "::getQueryResponseWithModel method expects data from database driver with key '{$key}' to be an object, but got '{$this->getType($response)}' instead",
                "Database Response Error", E_USER_ERROR, 0,
                PreviousException::getInstance(1));

            return new $model($response, $this->getTable(), $primaryKey);
        }

        if (!is_array($response)) $response = (array) $response;

        array_callback($response, function ($row, $key) use ($model, $primaryKey) {

            if (!is_object($row)) throw new QueRuntimeException(
                self::class . "::getQueryResponseWithModel method expects data with key '{$key}' from database driver to be an object, but got '{$this->getType($row)}' instead",
                "Database Response Error", E_USER_ERROR, 0,
                PreviousException::getInstance(2));

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
                $this->getResponseSize() > 0 : true);
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
     * @return int
     */
    public function getResponseSize(): int
    {
        return is_array($this->getDriverResponse()->getResponse()) ?
            array_size($this->getDriverResponse()->getResponse()) : 0;
    }

    /**
     * @param Closure $callback
     * @return bool
     */
    public function query_response_walk(Closure $callback): bool {

        if (!$this->isSuccessful() || !is_callable($callback)) return false;

        $response = $this->getQueryResponse();

        if (!is_iterable($response)) return false;

        foreach ($response as $row) $callback($row);

        return true;
    }

    /**
     * @param Closure $callback
     * @param string|null $model
     * @param string $primaryKey
     * @return bool
     */
    public function query_response_walk_with_model(Closure $callback, string $model = null, string $primaryKey = "id"): bool {

        if (!$this->isSuccessful() || !is_callable($callback)) return false;

        $model = \model(($modelKey = $model ?? config("database.default.model")));

        if ($model === null) throw new QueRuntimeException(
            "No database model was found with the key '{$modelKey}', check your database configuration to fix this issue.",
            "Que Runtime Error", E_USER_ERROR, HTTP_INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        if (!($implements = class_implements($model)) || !in_array(Model::class, $implements)) throw new QueRuntimeException(
            "The specified model ({$model}) with key '{$modelKey}' does not implement the Que database model interface.",
            "Que Runtime Error", E_USER_ERROR, HTTP_INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        $response = $this->getQueryResponse();

        if (!is_iterable($response)) return false;

        $count = 0;

        foreach ($response as $row) {
            if (!is_object($row)) continue;
            $callback(new $model($row, $this->getTable(), $primaryKey));
            $count++;
        }

        return $count > 0;
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
    private function getQueryType(): int
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
        if (!is_iterable($data) && !is_object($data)) return $data;

        iterable_callback_recursive($data, function ($value) {
            if (is_iterable($value) || is_object($value)) return $this->normalize_data($value);
            return is_null($value) ? null : ($this->get_mark_down($value) ?: stripslashes($value));
        });

        return $data;
    }

    /**
     * @param $data
     * @return mixed|null
     */
    private function get_mark_down($data) {
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
    private function getType($data) {
        return gettype($data);
    }

}