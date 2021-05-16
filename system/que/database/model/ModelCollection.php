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
use JetBrains\PhpStorm\Pure;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\database\interfaces\model\Model;
use que\http\HTTP;
use que\support\Arr;
use que\support\interfaces\QueArrayAccess;
use que\support\Obj;
use que\template\Pagination;

class ModelCollection implements QueArrayAccess
{

    /**
     * @var Model[]
     */
    private array $models = [];

    /**
     * @var string|null
     */
    public ?string $query_tag = null;

    /**
     * @var bool
     */
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
            "Que Runtime Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        if (!$value instanceof Model) throw new QueRuntimeException(
            self::class . " expects an instance of " .
            Model::class . " got " . (is_object($value) ? get_class($value) : gettype($value)) . " instead",
        "Que Runtime Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        $this->models[$offset] = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
        if ($this->static) throw new QueRuntimeException(
            "You cannot unset an item from a static " . self::class, "Que Runtime Error",
            E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));
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
     * @param string|null $query_tag
     */
    public function __construct(array $models, ?string $query_tag = null, bool $static = false)
    {
        $this->query_tag = $query_tag;
        $this->static = $static;
        foreach ($models as $model) {
            if (!$model instanceof Model) throw new QueRuntimeException(
                self::class . " expects an instance of " .
                Model::class . " got " . (is_object($model) ? get_class($model) : gettype($model)) . " instead",
                "Que Runtime Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

            $this->addModel($model);
        }
    }

    /**
     * @return string|null
     */
    public function getLinks(): ?string
    {
        return $this->query_tag ? Pagination::getInstance()->getLinks($this->query_tag) : null;
    }

    /**
     * @param bool $onlyViewable
     * @return array
     */
    public function getArray(bool $onlyViewable = false): array {
        $list = [];
        foreach ($this->models as $model) $list[] = $model->getArray($onlyViewable);
        return $list;
    }

    public function addViewable(string $viewable): void
    {
        // TODO: Implement addViewable() method.
        foreach ($this->models as $model) $model->addViewable($viewable);
    }

    public function setViewable(array $viewable): void
    {
        // TODO: Implement setViewable() method.
        foreach ($this->models as $model) $model->setViewable($viewable);
    }

    /**
     * @param bool $onlyViewable
     * @return object
     */
    public function getObject(bool $onlyViewable = false): object {
        $list = new \stdClass();
        foreach ($this->models as $key => $model) $list->{$key} = $model->getObject($onlyViewable);
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
            "You cannot add an item to a static " . self::class, "Que Runtime Error",
            E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));
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
    public function sum(Closure $callback): float|int
    {
        return array_sum($this->map($callback));
    }

    /**
     * Calculates and returns the sum of the values on each model in the collection
     * @param string $column
     * @return float|int
     */
    public function sumColumn(string $column): float|int
    {
        return array_sum($this->map(function (Model $model) use($column) {
            return $model->getValue($column);
        }));
    }

    /**
     * @return bool
     */
    public function refresh(): bool
    {
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
    public function update(array $columns, string $primaryKey = null): bool
    {
        $count = 0;
        foreach ($this->models as $model) {
            if (!$model instanceof Model) continue;
            if ($model->update($columns, $primaryKey)?->isSuccessful()) $count++;
        }
        return $count > 0;
    }

    /**
     * @param string|null $primaryKey
     * @return bool
     */
    public function delete(string $primaryKey = null): bool
    {
        $count = 0;
        foreach ($this->models as $key => $model) {
            if (!$model instanceof Model) continue;
            if ($model->delete($primaryKey)?->isSuccessful()) {
                if (!$this->static) $this->offsetUnset($key);
                $count++;
            }
        }
        return $count > 0;
    }

    /**
     * Set data to all models in collection
     * @param string $key
     * @param $value
     */
    public function set(string $key, $value) {
        foreach ($this->models as $model) {
            if (!$model instanceof Model) continue;
            $model->set($key, $value);
        }
    }

    /**
     * Unset data from all models in collection
     * @param string $key
     */
    public function unset(string $key) {
        foreach ($this->models as $model) {
            if (!$model instanceof Model) continue;
            $model->offsetUnset($key);
        }
    }

    /**
     * @param string $name
     * @param Closure|null $arguments
     * @return $this
     */
    public function load(string $name, Closure $arguments = null): ModelCollection {
        foreach ($this->models as $model) {
            if (!$model instanceof Model) continue;
            $this->__load($model, $name, $arguments);
        }
        return $this;
    }

    /**
     * @param Model $model
     * @param string $name
     * @param Closure|null $arguments
     */
    private function __load(Model $model, string $name, ?Closure $arguments = null) {
        if (!$arguments) {
            $model->load($name);
            return;
        }
        $arguments = $arguments($model);
        if (!is_array($arguments)) $arguments = [$arguments];
        $model->load($name, ...$arguments);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->models);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        // TODO: Implement getIterator() method.
        return new ArrayIterator($this->models);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
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
    public function serialize(): ?string
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

    #[Pure] public function array_keys(): array
    {
        // TODO: Implement array_keys() method.
        return array_keys($this->models);
    }

    #[Pure] public function array_values(): array
    {
        // TODO: Implement array_values() method.
        return array_values($this->models);
    }

    #[Pure] public function key(): int|string|null
    {
        // TODO: Implement key() method.
        return key($this->models);
    }

    #[Pure] public function current(): Model|bool
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
        return $this->models;
    }

    public function shuffle(): void
    {
        // TODO: Implement shuffle() method.
        shuffle($this->models);
    }
}
