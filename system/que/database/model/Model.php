<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/10/2018
 * Time: 2:15 PM
 */

namespace que\database\model;

use ArrayIterator;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\common\validator\interfaces\Condition;
use que\database\interfaces\model\Model as ModelAlias;
use que\database\interfaces\Builder;
use que\http\HTTP;

class Model implements ModelAlias
{

    /**
     * @var object
     */
    private object $object;

    /**
     * @var string
     */
    private string $table;

    /**
     * @var string
     */
    private string $primaryKey;

    /**
     * @inheritDoc
     */
    public function __construct(object &$tableRow, string $tableName, string $primaryKey = 'id')
    {
        $this->setObject($tableRow);
        $this->setTable($tableName);
        $this->setPrimaryKey($primaryKey);
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
    public function getInt($key, int $default = 0): int
    {
        return (int) $this->getValue($key, $default);
    }

    /**
     * @param $key
     * @param float $default
     * @return float
     */
    public function getFloat($key, float $default = 0.0): float
    {
        return (float) $this->getValue($key, $default);
    }

    /**
     * @inheritDoc
     */
    public function get($key): Condition
    {
        // TODO: Implement get() method.
        if (!$this->offsetExists($key))
            throw new QueRuntimeException("Undefined key: '{$key}' not found in current model object", "Model error",
                0, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        return new \que\common\validator\condition\Condition($key, $this->getValue($key));
    }

    /**
     * @return Builder
     */
    public function getNextRecord(): Builder {
        return db()->select()->table($this->getTable())
            ->where($this->getPrimaryKey(), $this->getValue($this->getPrimaryKey()), '>');
    }

    /**
     * @return Builder
     */
    public function getPreviousRecord(): Builder {
        return db()->select()->table($this->getTable())
            ->where($this->getPrimaryKey(), $this->getValue($this->getPrimaryKey()), '<');
    }

    /**
     * @return bool
     */
    public function refresh(): bool
    {
        $data = db()->find($this->getTable(), $this->getValue($this->getPrimaryKey()), $this->getPrimaryKey());
        if (!$data->isSuccessful()) return false;
        $this->object = $data->getQueryResponse(0);
        return true;
    }

    /**
     * @param array $columns
     * @param string|null $primaryKey
     * @return bool
     */
    public function update(array $columns, string $primaryKey = null): bool {

        if ($primaryKey === null) $primaryKey = $this->getPrimaryKey();

        if (!$this->offsetExists($primaryKey)) return false;

        $columnsToUpdate = [];

        foreach ($columns as $key => $value)
            if ($this->offsetExists($key)) $columnsToUpdate[$key] = $value;

        if (empty($columnsToUpdate)) return false;

        $update = db()->update()->table($this->getTable())->columns($columnsToUpdate)
            ->where($primaryKey, $this->getValue($primaryKey))->exec();

        if ($status = $update->isSuccessful())
            foreach ($columnsToUpdate as $key => $value) $this->offsetSet($key, $value);

        return $status;
    }

    /**
     * @param string|null $primaryKey
     * @return bool
     */
    public function delete(string $primaryKey = null): bool {

        if ($primaryKey === null) $primaryKey = $this->getPrimaryKey();

        if (!$this->offsetExists($primaryKey)) return false;

        $delete = db()->delete()->table($this->getTable())->where($primaryKey, $this->getValue($primaryKey))->exec();

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

    /**
     * @inheritDoc
     */
    public function offsetRename($offset, $to): void
    {
        // TODO: Implement offsetRename() method.
        $this->object->{$to} = $this->offsetGet($offset);
        if ($offset != $to) $this->offsetUnset($offset);
    }

    public function __clone()
    {
        // TODO: Implement __clone() method.
        $this->object = clone $this->object;
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        // TODO: Implement getIterator() method.
        return new ArrayIterator($this->getArray());
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        // TODO: Implement serialize() method.
        return serialize($this->object);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        // TODO: Implement unserialize() method.
        $this->object = unserialize($serialized);
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        // TODO: Implement count() method.
        array_size($this->getArray());
    }

    public function array_keys(): array
    {
        // TODO: Implement array_keys() method.
        return array_keys($this->getArray());
    }

    public function array_values(): array
    {
        // TODO: Implement array_values() method.
        return array_values($this->getArray());
    }

    public function key()
    {
        // TODO: Implement key() method.
        return key($this->getArray());
    }

    public function current()
    {
        // TODO: Implement current() method.
        return current($this->getArray());
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
        return json_encode($this->getArray());
    }

    public function shuffle(): void
    {
        // TODO: Implement shuffle() method.
    }
}
