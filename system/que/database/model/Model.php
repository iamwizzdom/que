<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/10/2018
 * Time: 2:15 PM
 */

namespace que\database\model;

use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\database\model\interfaces\Condition;
use que\database\model\interfaces\Model as ModelAlias;

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
                0, HTTP_INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        return new \que\database\model\Condition($key, $this->getValue($key));
    }

    /**
     * @param array $columns
     * @param string $dataType
     * @param array|null $join
     * @param string|null $primaryKey
     * @return array|object|ModelAlias|null
     */
    public function getNextRecord(array $columns = ['*'], string $dataType = 'model', array $join = null, string $primaryKey = null) {

        if ($primaryKey === null) $primaryKey = $this->getPrimaryKey();

        if (!$this->offsetExists($primaryKey)) return null;

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
     * @param array $columns
     * @param string $dataType
     * @param array|null $join
     * @param string|null $primaryKey
     * @return array|object|ModelAlias|null
     */
    public function getPreviousRecord(array $columns = ['*'], string $dataType = 'model', array $join = null, string $primaryKey = null) {

        if ($primaryKey === null) $primaryKey = $this->getPrimaryKey();

        if (!$this->offsetExists($primaryKey)) return null;

        if (!$this->get($primaryKey)->isNumeric()) {
            throw new QueRuntimeException("Invalid primary key: Value for key '{$primaryKey}' is expected to be numeric, got {$this->get($primaryKey)->getType()}",
                "Model error", 0, HTTP_INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));
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
     * @return bool
     */
    public function refresh(): bool
    {
        $data = db()->find($this->getTable(), $this->getPrimaryKey(), $this->getValue(
            $this->getPrimaryKey()
        ));
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
     * @param string|null $primaryKey
     * @return bool
     */
    public function delete(string $primaryKey = null): bool {

        if ($primaryKey === null) $primaryKey = $this->getPrimaryKey();

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
}