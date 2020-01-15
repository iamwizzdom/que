<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/10/2018
 * Time: 2:15 PM
 */

namespace que\model;

use ArrayAccess;
use que\common\exception\QueException;
use que\error\RuntimeError;

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
        return isset($this->object->{$key});
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
     * @param null $default
     * @return int
     */
    public function getInt($key, $default = null)
    {
        return (int) $this->getValue($key, $default);
    }

    /**
     * @param $key
     * @param null $default
     * @return float
     */
    public function getFloat($key, $default = null)
    {
        return (float) $this->getValue($key, $default);
    }

    /**
     * @param $key
     * @return Condition
     */
    public function get($key): Condition {
        try {

            if (!isset($this->object->{$key}))
                throw new QueException("Undefined key: '{$key}' not found in current model object", "Model Error");

            return new Condition($key, $this->object->{$key});
        } catch (QueException $e) {

            RuntimeError::render(E_USER_ERROR, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(), $e->getTitle());
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
            if (isset($this->object->{$key}))
                $columnsToUpdate[$key] = $value;

        if (empty($columnsToUpdate)) return false;

        $update = db()->update($this->getTable(), $columnsToUpdate, [
            'AND' => [
                $primaryKey => $this->object->{$primaryKey}
            ]
        ]);

        if ($status = $update->isSuccessful())
            foreach ($columnsToUpdate as $key => $value)
                $this->object->{$key} = $value;

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
                $primaryKey => $this->object->{$primaryKey}
            ]
        ]);

        if ($status = $delete->isSuccessful()) $this->object = null;

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
        return isset($this->object->{$offset});
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
}