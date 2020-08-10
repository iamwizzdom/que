<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 5/9/2020
 * Time: 7:20 PM
 */

namespace custom\model;

use Exception;
use que\common\validator\interfaces\Condition;
use que\database\interfaces\model\Model;

class CustomModel implements Model
{
    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        // TODO: Implement getIterator() method.
    }

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

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        // TODO: Implement serialize() method.
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        // TODO: Implement unserialize() method.
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        // TODO: Implement count() method.
    }

    /**
     * @inheritDoc
     */
    public function __construct(object &$tableRow, string $tableName, string $primaryKey = 'id')
    {
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

    public function getNextRecord(): \que\database\interfaces\Builder
    {
        // TODO: Implement getNextRecord() method.
    }

    public function getPreviousRecord(): \que\database\interfaces\Builder
    {
        // TODO: Implement getPreviousRecord() method.
    }

    /**
     * @inheritDoc
     */
    public function refresh(): bool
    {
        // TODO: Implement refresh() method.
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

    public function array_keys(): array
    {
        // TODO: Implement array_keys() method.
    }

    public function array_values(): array
    {
        // TODO: Implement array_values() method.
    }

    public function key()
    {
        // TODO: Implement key() method.
    }

    public function current()
    {
        // TODO: Implement current() method.
    }

    public function shuffle(): void
    {
        // TODO: Implement shuffle() method.
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
    }
}
