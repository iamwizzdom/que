<?php


namespace que\database\model\base;


use ArrayIterator;
use JetBrains\PhpStorm\Pure;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\common\validator\interfaces\Condition;
use que\database\interfaces\model\Model;
use que\database\model\ModelCollection;
use que\http\HTTP;
use que\support\Arr;
use que\support\Obj;
use relation\DbMapper;
use Traversable;

abstract class BaseModel implements Model
{
    use DbMapper;

    /**
     * @var string
     */
    protected string $modelKey = "que";

    /**
     * @var array
     */
    protected array $copy = [];

    /**
     * @var array
     */
    protected array $appends = [];

    /**
     * @var array
     */
    protected array $casts = [];

    /**
     * @var array
     */
    protected array $hidden = [];

    /**
     * @var array
     */
    protected array $renames = [];

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
        $this->__copy();
        $this->__append();
        $this->__cast();
        $this->__rename();
    }

    /**
     * @inheritDoc
     */
    public function getModelKey(): string
    {
        // TODO: Implement getModelKey() method.
        return $this->modelKey;
    }

    /**
     * @inheritDoc
     */
    public function &getObject(): object
    {
        // TODO: Implement getObject() method.
        return $this->object;
    }

    /**
     * @param object $object
     */
    private function setObject(object &$object)
    {
        $this->object = &$object;
    }

    /**
     * @inheritDoc
     */
    #[Pure] public function getArray(): array
    {
        // TODO: Implement getArray() method.
        return object_to_array(Obj::exclude($this->object, ...$this->hidden));
    }

    /**
     * @inheritDoc
     */
    public function getTable(): string
    {
        // TODO: Implement getTable() method.
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
     * @inheritDoc
     */
    public function getPrimaryKey(): string
    {
        // TODO: Implement getPrimaryKey() method.
        return $this->primaryKey;
    }

    /**
     * @param string $primaryKey
     */
    private function setPrimaryKey(string $primaryKey): void
    {
        $this->primaryKey = $primaryKey;
    }

    public function setModelKey(string $modelKey): void
    {
        // TODO: Implement setModelKey() method.
        $this->modelKey = $modelKey;
    }

    /**
     * @return array
     */
    public function getAppends(): array
    {
        return $this->appends;
    }

    public function setAppends(array $appends): void
    {
        // TODO: Implement setAppends() method.
        $this->appends = $appends;
    }

    /**
     * @return array
     */
    public function getHidden(): array
    {
        return $this->hidden;
    }

    /**
     * @param array $hidden
     */
    public function setHidden(array $hidden): void
    {
        $this->hidden = $hidden;
    }

    /**
     * @param array $casts
     */
    public function setCasts(array $casts): void
    {
        $this->casts = $casts;
    }

    /**
     * @param array $renames
     */
    public function setRenames(array $renames): void
    {
        $this->renames = $renames;
    }

    /**
     * @inheritDoc
     */
    public function has($key): bool
    {
        // TODO: Implement has() method.
        return $this->offsetExists($key);
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value): void
    {
        // TODO: Implement set() method.
        $this->offsetSet($key, $value);
    }


    /**
     * @inheritDoc
     */
    public function isEmpty($key): bool
    {
        // TODO: Implement isEmpty() method.
        if (!$this->offsetExists($key)) return true;
        $value = $this->getValue($key);
        return empty($value) && $value != "0";
    }

    /**
     * @inheritDoc
     */
    #[Pure] public function getValue($key, $default = null)
    {
        // TODO: Implement getValue() method.
        return Obj::get($this->object, $key, $default);
    }

    /**
     * @inheritDoc
     */
    #[Pure] public function getInt($key, int $default = 0): int
    {
        // TODO: Implement getInt() method.
        return (int) $this->getValue($key, $default);
    }

    /**
     * @inheritDoc
     */
    #[Pure] public function getFloat($key, float $default = 0.0): float
    {
        // TODO: Implement getFloat() method.
        return (float) $this->getValue($key, $default);
    }

    /**
     * @inheritDoc
     */
    #[Pure] public function getBool($key, bool $default = false): bool
    {
        // TODO: Implement getBool() method.
        return (bool) $this->getInt($key, $default);
    }

    /**
     * @inheritDoc
     */
    public function validate($key): Condition
    {
        // TODO: Implement validate() method.
        if (!$this->offsetExists($key))
            throw new QueRuntimeException("Undefined key: '{$key}' not found in current model object", "Model Error",
                0, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        return new \que\common\validator\condition\Condition($key, $this->getValue($key));
    }

    /**
     * @inheritDoc
     */
    public function getNextRecord(): ?\que\database\interfaces\model\Model
    {
        // TODO: Implement getNextRecord() method.
        $record = db()->select()->table($this->getTable())->limit(1)
            ->where($this->getPrimaryKey(), $this->getValue($this->getPrimaryKey()), '>')->exec();
        $record->setModelKey($this->modelKey);
        return $record->isSuccessful() ? $record->getFirstWithModel($this->primaryKey) : null;
    }

    /**
     * @inheritDoc
     */
    public function getPreviousRecord(): ?\que\database\interfaces\model\Model
    {
        // TODO: Implement getPreviousRecord() method.
        $record = db()->select()->table($this->getTable())->limit(1)
            ->where($this->getPrimaryKey(), $this->getValue($this->getPrimaryKey()), '<')->exec();
        $record->setModelKey($this->modelKey);
        return $record->isSuccessful() ? $record->getFirstWithModel($this->primaryKey) : null;
    }

    /**
     * @inheritDoc
     */
    public function belongTo(string $table, string $foreignKey, string $primaryKey = "id", string $modelKey = "que"): ?\que\database\interfaces\model\Model
    {
        // TODO: Implement belongTo() method.
        return !$this->isEmpty($foreignKey) ? $this->oneToOne($table, $this->getValue($foreignKey), $primaryKey, $modelKey) : null;
    }

    public function hasOne(string $table, string $foreignKey, string $primaryKey = "id", string $modelKey = "que"): ?Model
    {
        // TODO: Implement hasOne() method.
        return !$this->isEmpty($primaryKey) ? $this->oneToOneRevered($table, $this->getValue($primaryKey), $foreignKey, $modelKey) : null;
    }


    /**
     * @inheritDoc
     */
    public function hasMany(string $table, string $foreignKey, string $primaryKey = "id", string $modelKey = "que"): ?ModelCollection
    {
        // TODO: Implement hasMany() method.
        return !$this->isEmpty($primaryKey) ? $this->oneToMany($table, $this->getValue($primaryKey), $foreignKey, $modelKey) : null;
    }

    /**
     * @inheritDoc
     */
    public function refresh(): bool
    {
        // TODO: Implement refresh() method.
        $data = db()->find($this->getTable(), $this->getValue($this->getPrimaryKey()), $this->getPrimaryKey());
        if (!$data->isSuccessful()) return false;
        $this->object = (object) $data->getFirst();
        return true;
    }

    /**
     * @inheritDoc
     */
    public function update(array $columns, string $primaryKey = null): bool
    {
        // TODO: Implement update() method.
        if (empty($columns)) return false;

        if ($primaryKey === null) $primaryKey = $this->getPrimaryKey();

        if (!$this->offsetExists($primaryKey)) return false;

        $update = db()->update()->table($this->getTable())->columns($columns)
            ->where($primaryKey, $this->getValue($primaryKey))->exec();

        if ($status = $update->isSuccessful())
            foreach ($columns as $key => $value)
                if ($this->offsetExists($key)) $this->offsetSet($key, $value);

        return $status;
    }

    /**
     * @inheritDoc
     */
    public function delete(string $primaryKey = null): bool
    {
        // TODO: Implement delete() method.
        if ($primaryKey === null) $primaryKey = $this->getPrimaryKey();

        if (!$this->offsetExists($primaryKey)) return false;

        $delete = db()->delete()->table($this->getTable())->where($primaryKey, $this->getValue($primaryKey))->exec();

        if ($status = $delete->isSuccessful()) $this->object = (object)[];

        return $status;
    }

    /**
     * @inheritDoc
     */
    #[Pure] public function array_keys(): array
    {
        // TODO: Implement array_keys() method.
        return array_keys($this->getArray());
    }

    /**
     * @inheritDoc
     */
    #[Pure] public function array_values(): array
    {
        // TODO: Implement array_values() method.
        return array_values($this->getArray());
    }

    /**
     * @inheritDoc
     */
    #[Pure] public function key(): int|string|null
    {
        // TODO: Implement key() method.
        return key($this->getArray());
    }

    /**
     * @inheritDoc
     */
    #[Pure] public function current(): mixed
    {
        // TODO: Implement current() method.
        return current($this->getArray());
    }


    public function shuffle(): void
    {
        // TODO: Implement shuffle() method.
    }


    /**
     * @inheritDoc
     */
    #[Pure] public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
        return $this->getArray();
    }

    /**
     * @inheritDoc
     */
    public function serialize(): ?string
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
    public function count(): int
    {
        // TODO: Implement count() method.
        return array_size($this->getArray());
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable|ArrayIterator
    {
        // TODO: Implement getIterator() method.
        return new ArrayIterator($this->getArray());
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        // TODO: Implement offsetExists() method.
        return Obj::has($this->object, $offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
        return Obj::get($this->object, $offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
        Obj::set($this->object, $offset, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
        Obj::unset($this->object, $offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetRename($from, $to): void
    {
        // TODO: Implement offsetRename() method.
        $this->object = Obj::rename_key($this->object, $from, $to);
    }

    /**
     * @param string $name
     * @param mixed ...$arguments
     * @return Model
     */
    public function load(string $name, ...$arguments): Model {
        $this->set($name, $this->__call($name, $arguments));
        return $this;
    }

    public function __clone(): void
    {
        // TODO: Implement __clone() method.
        $this->object = clone $this->object;
    }

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement __call() method.
        if (!method_exists($this, $name)) {
            $name = strtolower($name);
            $name = explode("_", $name);

            Arr::callback($name, function ($method) {
                return ucfirst($method);
            });

            $name = ("get" . implode("", $name));
        }
        return method_exists($this, $name) ? $this->{$name}(...$arguments) : null;
    }

    private function __rename() {
        if (!empty($this->renames)) {
            foreach ($this->renames as $from => $to) $this->offsetRename($from, $to);
        }
    }

    private function __cast() {
        if (!empty($this->casts)) {
            foreach ($this->casts as $column => $cast) {
                if (!$this->offsetExists($column)) continue;
                $operand = null;
                if (str_contains($cast, ":")) {
                    $data = explode(":", $cast);
                    $cast = $data[0];
                    $operand = $data[1] ?? null;
                }
                switch ($cast) {
                    case 'json':
                    case 'array':
                        $this->offsetSet($column, is_string($value = $this->getValue($column)) ? json_decode($value, true) : (array) $value);
                        break;
                    case 'object':
                        $this->offsetSet($column, is_string($value = $this->getValue($column)) ? json_decode($value) : (object) $value);
                        break;
                    case 'int':
                    case 'integer':
                        $this->offsetSet($column, $this->getInt($column));
                        break;
                    case 'string':
                        $this->offsetSet($column, (string) $this->getValue($column));
                        break;
                    case 'bool':
                    case 'boolean':
                        $this->offsetSet($column, $this->getBool($column));
                        break;
                    case 'double':
                        $this->offsetSet($column, (double) $this->getValue($column));
                        break;
                    case 'real':
                    case 'float':
                        $this->offsetSet($column, $this->getFloat($column));
                        break;
                    case 'time_ago':
                        $this->offsetSet($column, _time()->time_ago($this->getValue($column)));
                        break;
                    case 'date':
                    case 'time':
                    case 'datetime':
                        $value = $this->getValue($column);
                        $operand = $operand ?: DATE_FORMAT_MYSQL;
                        if (is_numeric($value)) $value = date($operand, $value);
                        elseif ($this->validate($column)->isDate()) {
                            $value = $this->validate($column)->toDate($operand)->getValue();
                        }
                        $this->offsetSet($column, $value);
                        break;
                    case 'func':
                        $this->offsetSet($column, $this->validate($column)->_call($operand)->getValue());
                        break;
                    default:
                        break;
                }
            }
        }
    }

    private function __copy() {
        if (!empty($this->copy)) {
            foreach ($this->copy as $key => $alias) {
                $this->set($alias, $this->getValue($key));
            }
        }
    }

    private function __append() {
        if (!empty($this->appends = $this->getAppends())) {
            foreach ($this->appends as $alias => $method) {
                if (is_array($method)) {

                    if (is_callable($method['method'])) {

                        if (!empty($method['args'] ?? [])) {
                            if (is_iterable($method['args'])) $this->object->{$alias} = $method['method'](...$method['args']);
                            else $this->object->{$alias} = $method['method']($method['args']);
                        } else $this->object->{$alias} = $method['method']();

                        continue;
                    }

                    $alias = $method['method'] = strtolower($method['method']);
                    $method['method'] = explode("_", $method['method']);

                    Arr::callback($method['method'], function ($method) {
                        return ucfirst($method);
                    });

                    $method['method'] = ("get" . implode("", $method['method']));

                    if (!method_exists($this, $method['method'])) continue;

                    if (!empty($method['args'] ?? [])) {
                        if (is_iterable($method['args'])) $this->object->{$alias} = $this->{$method['method']}(...$method['args']);
                        else $this->object->{$alias} = $this->{$method['method']}($method['args']);
                    } else $this->object->{$alias} = $this->{$method['method']}();

                } elseif (is_string($method)) {

                    $alias = $method = strtolower($method);
                    $method = explode("_", $method);

                    Arr::callback($method, function ($method) {
                        return ucfirst($method);
                    });

                    $method = ("get" . implode("", $method));

                    if (!method_exists($this, $method)) continue;

                    $this->object->{$alias} = $this->{$method}();

                } elseif (is_callable($method)) {

                    $this->object->{$alias} = $this->{$method}();
                }
            }
        }
    }
}
