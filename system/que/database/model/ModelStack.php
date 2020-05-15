<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 5/10/2020
 * Time: 9:45 PM
 */

namespace que\database\model;


use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\database\model\interfaces\Model;

class ModelStack implements ArrayAccess, IteratorAggregate, Countable
{

    /**
     * @var array
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
     * ModelStack constructor.
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
     * @return array || Array of response from all models
     */
    public function runOnAll(Closure $callback): array
    {
        $response = [];
        foreach ($this->models as $model) {
            if (!$model instanceof Model) continue;
            $response[] = $callback($model);
        }
        return $response;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->models);
    }

    /**
     * @inheritDoc
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
}