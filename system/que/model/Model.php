<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/10/2018
 * Time: 2:15 PM
 */

namespace que\model;

use ArrayAccess;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;

class Model implements ArrayAccess
{

    /**
     * @var object
     */
    private $object;

    /**
     * @var string
     */
    private $table;

    /**
     * Model constructor.
     * @param object $table_row
     * @param string $table
     */
    public function __construct(object &$table_row, string $table)
    {
        $this->setObject($table_row);
        $this->setTable($table);
    }

    /**
     * @return object
     */
    public function &getObject(): object
    {
        return $this->object;
    }

    /**
     * @return array
     */
    public function getArray(): array
    {
        return object_to_array($this->object);
    }

    /**
     * @param object $object
     */
    private function setObject(object &$object)
    {
        $this->object = &$object;
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
     * @param $key
     * @return bool
     */
    public function has($key): bool {
        return $this->offsetExists($key);
    }

    /**
     * @param $key
     * @return bool
     */
    public function isEmpty($key): bool {
        return empty($this->object->{$key}) && $this->object->{$key} != "0";
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getValue($key, $default = null)
    {
        return $this->object->{$key} ?? $default;
    }

    /**
     * @param $key
     * @param int $default
     * @return int
     */
    public function getInt($key, int $default = 0)
    {
        return (int) $this->getValue($key, $default);
    }

    /**
     * @param $key
     * @param float $default
     * @return float
     */
    public function getFloat($key, float $default = 0.0)
    {
        return (float) $this->getValue($key, $default);
    }

    /**
     * @param $key
     * @return Condition
     */
    public function get($key): Condition {

        if (!$this->offsetExists($key))
            throw new QueRuntimeException("Undefined key: '{$key}' not found in current model object", "Model error",
                0, HTTP_INTERNAL_ERROR_CODE, PreviousException::getInstance(1));

        return new Condition($key, $this->getValue($key));
    }

    /**
     * @param string $primaryKey
     * @param array $columns
     * @param string $dataType
     * @param array|null $join
     * @return array|mixed|Model|null
     */
    public function nextRecord(string $primaryKey, array $columns = ['*'], string $dataType = 'model', array $join = null) {

        if (!$this->offsetExists($primaryKey)) return null;

        if (!$this->get($primaryKey)->isNumeric()) {
            throw new QueRuntimeException("Invalid primary key: Value for key '{$primaryKey}' is expected to be numeric, got {$this->get($primaryKey)->getType()}",
                "Model error", 0, HTTP_INTERNAL_ERROR_CODE, PreviousException::getInstance(1));
        }

        $record = db()->select($this->getTable(), implode(',', $columns), [
            'AND' => [
                "{$primaryKey}[>]" => $this->getValue($primaryKey)
            ]
        ], $join, 1);

        if (!$record->isSuccessful()) return null;

        switch (strtolower($dataType)) {
            case 'model':
                return $record->getQueryResponseWithModel(0);
            case 'array':
                return $record->getQueryResponseArray(0);
            default:
                return $record->getQueryResponse(0);
        }

    }

    /**
     * @param string $primaryKey
     * @param array $columns
     * @param string $dataType
     * @param array|null $join
     * @return array|mixed|Model|null
     */
    public function previousRecord(string $primaryKey, array $columns = ['*'], string $dataType = 'model', array $join = null) {

        if (!$this->offsetExists($primaryKey)) return null;

        if (!$this->get($primaryKey)->isNumeric()) {
            throw new QueRuntimeException("Invalid primary key: Value for key '{$primaryKey}' is expected to be numeric, got {$this->get($primaryKey)->getType()}",
                "Model error", 0, HTTP_INTERNAL_ERROR_CODE, PreviousException::getInstance(1));
        }

        $record = db()->select($this->getTable(), implode(',', $columns), [
            'AND' => [
                "{$primaryKey}[<]" => $this->getValue($primaryKey)
            ]
        ], $join, 1, [$primaryKey => 'DESC']);

        if (!$record->isSuccessful()) return null;

        switch (strtolower($dataType)) {
            case 'model':
                return $record->getQueryResponseWithModel(0);
            case 'array':
                return $record->getQueryResponseArray(0);
            default:
                return $record->getQueryResponse(0);
        }

    }

    /**
     * @param string $primaryKey
     * @param array $columns
     * @return bool
     */
    public function update(string $primaryKey, array $columns): bool {

        if (!$this->offsetExists($primaryKey)) return false;

        $columnsToUpdate = [];

        foreach ($columns as $key => $value)
            if ($this->offsetExists($key)) $columnsToUpdate[$key] = $value;

        if (empty($columnsToUpdate)) return false;

        $update = db()->update($this->getTable(), $columnsToUpdate, [
            'AND' => [
                $primaryKey => $this->getValue($primaryKey)
            ]
        ]);

        if ($status = $update->isSuccessful())
            foreach ($columnsToUpdate as $key => $value) $this->offsetSet($key, $value);

        return $status;

    }

    /**
     * @param string $primaryKey
     * @return bool
     */
    public function delete(string $primaryKey) {

        if (!$this->offsetExists($primaryKey)) return false;

        $delete = db()->delete($this->getTable(), [
            'AND' => [
                $primaryKey => $this->getValue($primaryKey)
            ]
        ]);

        if ($status = $delete->isSuccessful()) $this->object = (object)[];

        return $status;
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
        return object_key_exists($offset, $this->object);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
        return $this->object->{$offset} ?? null;
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
        $this->object->{$offset} = $value;
    }


    public function offsetRename($offset, $to)
    {
        // TODO: Implement offsetSet() method.
        $this->object->{$to} = $this->offsetGet($offset);
        $this->offsetUnset($offset);
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
        unset($this->object->{$offset});
    }
}