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
use que\support\interfaces\QueArrayAccess;

interface Model extends QueArrayAccess
{
    /**
     * Model constructor.
     * @param object $tableRow
     * @param string $tableName
     * @param string $primaryKey
     */
    public function __construct(
        object &$tableRow,
        string $tableName,
        string $primaryKey = 'id'
    );

    /**
     * @return string
     */
    public function getModelKey(): string;

    /**
     * @return object
     */
    public function &getObject(): object;

    /**
     * @return array
     */
    public function getArray(): array;

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
     * @return bool
     */
    public function refresh(): bool;

    /**
     * @param array $columns
     * @param string|null $primaryKey
     * @return bool
     */
    public function update(array $columns, string $primaryKey = null): bool;

    /**
     * @param string|null $primaryKey
     * @return bool
     */
    public function delete(string $primaryKey = null): bool;

    /**
     * @param $offset
     * @param $to
     */
    public function offsetRename($offset, $to): void;

}
