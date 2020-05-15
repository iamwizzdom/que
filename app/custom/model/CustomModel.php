<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 5/9/2020
 * Time: 7:20 PM
 */

namespace custom\model;


use que\database\model\interfaces\Condition;
use que\database\model\interfaces\Model;

class CustomModel implements Model
{
    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }

    private object $tableRow;

    /**
     * @inheritDoc
     */
    public function __construct(object &$tableRow, string $tableName, string $primaryKey = 'id')
    {
        $this->tableRow = $tableRow;
    }

    /**
     * @inheritDoc
     */
    public function &getObject(): object
    {
        // TODO: Implement getObject() method.
    }

    /**
     * @inheritDoc
     */
    public function getArray(): array
    {
        // TODO: Implement getArray() method.
    }

    /**
     * @inheritDoc
     */
    public function getTable(): string
    {
        // TODO: Implement getTable() method.
    }

    /**
     * @inheritDoc
     */
    public function getPrimaryKey(): string
    {
        // TODO: Implement getPrimaryKey() method.
    }

    /**
     * @inheritDoc
     */
    public function has($key): bool
    {
        // TODO: Implement has() method.
    }

    /**
     * @inheritDoc
     */
    public function isEmpty($key): bool
    {
        // TODO: Implement isEmpty() method.
    }

    /**
     * @inheritDoc
     */
    public function getValue($key, $default = null)
    {
        // TODO: Implement getValue() method.
    }

    /**
     * @inheritDoc
     */
    public function getInt($key, int $default = 0): int
    {
        // TODO: Implement getInt() method.
    }

    /**
     * @inheritDoc
     */
    public function getFloat($key, float $default = 0.0): float
    {
        // TODO: Implement getFloat() method.
    }

    /**
     * @inheritDoc
     */
    public function get($key): Condition
    {
        // TODO: Implement get() method.
    }

    /**
     * @inheritDoc
     */
    public function getNextRecord(array $columns = ['*'], string $dataType = 'model', array $join = null, string $primaryKey = null)
    {
        // TODO: Implement getNextRecord() method.
    }

    /**
     * @inheritDoc
     */
    public function getPreviousRecord(array $columns = ['*'], string $dataType = 'model', array $join = null, string $primaryKey = null)
    {
        // TODO: Implement getPreviousRecord() method.
    }

    /**
     * @inheritDoc
     */
    public function update(array $columns, string $primaryKey = null): bool
    {
        // TODO: Implement update() method.
    }

    /**
     * @inheritDoc
     */
    public function delete(string $primaryKey = null): bool
    {
        // TODO: Implement delete() method.
    }

    /**
     * @inheritDoc
     */
    public function offsetRename($offset, $to): void
    {
        // TODO: Implement offsetRename() method.
    }
}