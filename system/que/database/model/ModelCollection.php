<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 5/10/2020
 * Time: 9:45 PM
 */

namespace que\database\model;


use ArrayIterator;
use Closure;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\database\interfaces\model\Model;
use que\support\interfaces\QueArrayAccess;

class ModelCollection implements QueArrayAccess
{

    /**
     * @var Model[]
     */
    private array $models = [];

    private bool $static;

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
        return isset($this->models[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
        return $this->models[$offset] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
        if ($this->static) throw new QueRuntimeException(
            "You cannot add an item to a static " . self::class,
            "Que Runtime Error", E_USER_ERROR, 0, PreviousException::getInstance(1));

        if (!$value instanceof Model) throw new QueRuntimeException(
            self::class . " expects an instance of " .
            Model::class . " got " . (is_object($value) ? get_class($value) : gettype($value)) . " instead",
        "Que Runtime Error", E_USER_ERROR, 0, PreviousException::getInstance(1));

        $this->models[$offset] = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
        if ($this->static) throw new QueRuntimeException(
            "You cannot unset an item from a static " . self::class,
            "Que Runtime Error", E_USER_ERROR, 0, PreviousException::getInstance(1));
        unset($this->models[$offset]);
    }

    /**
     * @param Model $model
     * @return int
     */
    public function detach(Model $model): int
    {
        return $this->unsetWhen(function (Model $m) use ($model) {
            return $m->validate($m->getPrimaryKey())->isEqual($model->getValue($model->getPrimaryKey()));
        });
    }

    /**
     * ModelCollection constructor.
     * @param array $models
     * @param bool $static
     */
    public function __construct(array $models, bool $static = false)
    {
        $this->static = $static;
        foreach ($models as $model) {
            if (!$model instanceof Model) throw new QueRuntimeException(
                self::class . " expects an instance of " .
                Model::class . " got " . (is_object($model) ? get_class($model) : gettype($model)) . " instead",
                "Que Runtime Error", E_USER_ERROR, 0, PreviousException::getInstance(1));

            $this->addModel($model);
        }
    }

    /**
     * @return array
     */
    public function getArray(): array {
        $list = [];
        foreach ($this->models as $model) $list[] = $model->getArray();
        return $list;
    }

    /**
     * @return object
     */
    public function getObject(): object {
        $list = new \stdClass();
        foreach ($this->models as $key => $model) $list->{$key} = $model->getObject();
        return $list;
    }


    /**
     * @return Model[]
     */
    public function getModels(): array
    {
        // TODO: Implement getModels() method.
        return $this->models;
    }

    /**
     * @param $key
     * @return Model|null
     */
    public function getModel($key): ?Model
    {
        // TODO: Implement getModel() method.
        return $this->models[$key] ?? null;
    }

    /**
     * @param Model $model
     */
    public function addModel(Model $model)
    {
        // TODO: Implement addToSTack() method.
        if ($this->static) throw new QueRuntimeException(
            "You cannot add an item to a static " . self::class,
            "Que Runtime Error", E_USER_ERROR, 0, PreviousException::getInstance(1));
        array_push($this->models, $model);
    }

    /**
     * Finds a model using the callback
     * @param Closure $callback
     * @return Model|null
     */
    public function find(Closure $callback): ?Model {
        foreach ($this->models as $model) {
            if ($callback($model)) return $model;
        }
        return null;
    }

    public function clear()
    {
        $this->models = [];
    }

    /**
     * @param Closure $callback | This callback will receive a Model instance as it's first param
     * @return bool
     */
    public function isTrueForAny(Closure $callback): bool
    {
        foreach ($this->models as $model) {
            if ($callback($model)) return true;
        }
        return false;
    }

    /**
     * @param Closure $callback | This callback will receive a Model instance as it's first param
     * @return bool
     */
    public function isNotTrueForAny(Closure $callback): bool
    {
        $count = 0;
        foreach ($this->models as $model) {
            if ($callback($model)) {
                $count++;
                break;
            }
        }
        return $count == 0;
    }

    /**
     * @param Closure $callback | This callback will receive a Model instance as it's first param
     * @return int || Number of items removed
     */
    public function unsetWhen(Closure $callback): int
    {
        $count = 0;
        foreach ($this->models as $key => $model) {
            if ($callback($model)) {
                $this->offsetUnset($key);
                $count++;
            }
        }
        return $count;
    }

    /**
     * @param Closure $callback | This callback will receive a Model instance as it's first param
     * @return array || Array of callback responses for all callbacks
     */
    public function map(Closure $callback): array
    {
        $response = [];
        foreach ($this->models as $model) {
            if (!$model instanceof Model) continue;
            $response[] = $callback($model);
        }
        return $response;
    }

    /**
     * Calculates and returns the sum of the values returned by the callback on each model in the collection
     * @param Closure $callback
     * @return float|int
     */
    public function sum(Closure $callback) {
        return array_sum($this->map($callback));
    }

    /**
     * @return bool
     */
    public function refresh() {
        $count = 0;
        foreach ($this->models as $model) {
            if (!$model instanceof Model) continue;
            if ($model->refresh()) $count++;
        }
        return $count > 0;
    }

    /**
     * @param array $columns
     * @param string|null $primaryKey
     * @return bool
     */
    public function update(array $columns, string $primaryKey = null) {
        $count = 0;
        foreach ($this->models as $model) {
            if (!$model instanceof Model) continue;
            if ($model->update($columns, $primaryKey)) $count++;
        }
        return $count > 0;
    }

    /**
     * @param string|null $primaryKey
     * @return bool
     */
    public function delete(string $primaryKey = null) {
        $count = 0;
        foreach ($this->models as $key => $model) {
            if (!$model instanceof Model) continue;
            if ($model->delete($primaryKey)) {
                if (!$this->static) $this->offsetUnset($key);
                $count++;
            }
        }
        return $count > 0;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->models);
    }

    /**
     * @return Model[]
     */
    public function getIterator()
    {
        // TODO: Implement getIterator() method.
        return new ArrayIterator($this->models);
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        // TODO: Implement count() method.
        return array_size($this->models);
    }

    public function __clone()
    {
        // TODO: Implement __clone() method.
        $this->clear();
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        // TODO: Implement serialize() method.
        return serialize($this->models);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        // TODO: Implement unserialize() method.
       $this->models = unserialize($serialized);
    }

    public function array_keys(): array
    {
        // TODO: Implement array_keys() method.
        return array_keys($this->models);
    }

    public function array_values(): array
    {
        // TODO: Implement array_values() method.
        return array_values($this->models);
    }

    public function key()
    {
        // TODO: Implement key() method.
        return key($this->models);
    }

    public function current()
    {
        // TODO: Implement current() method.
        return current($this->models);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
        return json_encode($this->models);
    }

    public function shuffle(): void
    {
        // TODO: Implement shuffle() method.
        shuffle($this->models);
    }
}
