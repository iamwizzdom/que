<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/19/2020
 * Time: 9:26 PM
 */

namespace que\database\interfaces\model;

use que\common\validator\interfaces\Condition;
use que\database\interfaces\Builder;
use que\database\model\ModelCollection;
use que\database\model\ModelQueryResponse;
use que\support\interfaces\QueArrayAccess;

interface Model extends QueArrayAccess
{
    /**
     * Model constructor.
     * @param object $tableRow
     * @param string $tableName
     * @param string $primaryKey
     */
    public function __construct(object &$tableRow, string $tableName, string $primaryKey = 'id');

    /**
     * @return string
     */
    public function getModelKey(): string;

    /**
     * @param bool $onlyFillable
     * @return object
     */
    public function &getObject(bool $onlyFillable = false): object;

    /**
     * @param bool $onlyFillable
     * @return array
     */
    public function getArray(bool $onlyFillable = false): array;

    /**
     * @return array
     */
    public function getCopy(): array;

    public function addCopy(): ?array;

    /**
     * @param array $copy
     */
    public function setCopy(array $copy): void;

    /**
     * @return array
     */
    public function getAppends(): array;

    public function addAppends(): ?array;

    /**
     * @param array $appends
     */
    public function setAppends(array $appends): void;

    /**
     * @return array
     */
    public function getHidden(): array;

    public function addHidden(): ?array;

    /**
     * @param array $hidden
     */
    public function setHidden(array $hidden): void;

    /**
     * @return array
     */
    public function getCasts(): array;

    public function addCasts(): ?array;

    /**
     * @param array $casts
     */
    public function setCasts(array $casts): void;

    /**
     * @return array
     */
    public function getRenames(): array;

    public function addRenames(): ?array;

    /**
     * @param array $renames
     */
    public function setRenames(array $renames): void;

    /**
     * @return bool
     */
    public function hasFillable(): bool;

    /**
     * @param string $fillable
     */
    public function addFillable(string $fillable): void;

    /**
     * @param array $fillables
     */
    public function setFillable(array $fillables): void;

    /**
     * @return string
     */
    public function getTable(): string;

    /**
     * @return string
     */
    public function getPrimaryKey(): string;

    /**
     * @param $key
     * @return bool
     */
    public function has($key): bool;

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value): void;

    /**
     * @param $key
     * @return bool
     */
    public function isEmpty($key): bool;

    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function getValue($key, $default = null);

    /**
     * @param $key
     * @param int $default
     * @return int
     */
    public function getInt($key, int $default = 0): int;

    /**
     * @param $key
     * @param float $default
     * @return float
     */
    public function getFloat($key, float $default = 0.0): float;

    /**
     * @param $key
     * @param bool $default
     * @return bool
     */
    public function getBool($key, bool $default = false): bool;

    /**
     * @param $key
     * @return Model|null
     */
    public function getModel($key): ?Model;

    /**
     * @param $key
     * @return Condition
     */
    public function validate($key): Condition;

    /**
     * @return Model|null
     */
    public function getNextRecord(): ?Model;

    /**
     * @return Model|null
     */
    public function getPreviousRecord(): ?Model;

    /**
     * @param string $table
     * @param string $foreignKey
     * @param string $primaryKey
     * @param string $modelKey
     * @return Model|null
     */
    public function belongTo(string $table, string $foreignKey, string $primaryKey = "id", string $modelKey = "que"): ?Model;

    /**
     * @param string $table
     * @param string $foreignKey
     * @param string $primaryKey
     * @param string $modelKey
     * @return Model|null
     */
    public function hasOne(string $table, string $foreignKey, string $primaryKey = "id", string $modelKey = "que"): ?Model;

    /**
     * @param string $table
     * @param string $foreignKey
     * @param string $primaryKey
     * @param string $modelKey
     * @return ModelCollection|null
     */
    public function hasMany(string $table, string $foreignKey, string $primaryKey = "id", string $modelKey = "que"): ?ModelCollection;

    /**
     * @return bool
     */
    public function refresh(): bool;

    /**
     * @param array $columns
     * @param string|null $primaryKey
     * @return ModelQueryResponse|null
     */
    public function update(array $columns, string $primaryKey = null): ?ModelQueryResponse;

    /**
     * @param string|null $primaryKey
     * @return ModelQueryResponse|null
     */
    public function delete(string $primaryKey = null): ?ModelQueryResponse;

    /**
     * @param $from
     * @param $to
     */
    public function offsetRename($from, $to): void;

    /**
     * @param string $name
     * @param mixed ...$arguments
     */
    public function load(string $name, ...$arguments): Model;

    /**
     * @param Model $model
     * @return static
     */
    public static function cast(Model $model): self;

    public function __clone(): void;

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name);

    /**
     * @param string $name
     * @param $value
     */
    public function __set(string $name, $value): void;

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments): mixed;
}
