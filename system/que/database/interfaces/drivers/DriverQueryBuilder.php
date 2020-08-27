<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 5/3/2020
 * Time: 10:07 PM
 */

namespace que\database\interfaces\drivers;


use Closure;

interface DriverQueryBuilder
{
    const INSERT = 1;
    const SELECT = 2;
    const UPDATE = 3;
    const DELETE = 4;
    const COUNT = 5;
    const AVG = 6;
    const SUM = 7;
    const RAW_SELECT = 8;
    const RAW_OBJECT = 9;
    const RAW_QUERY = 10;
    const SHOW = 11;

    /**
     * DriverQueryBuilder constructor.
     * @param Driver $driver
     * @param array $bindings
     * @param bool $isSubQuery
     */
    public function __construct(Driver $driver, array $bindings = [], bool $isSubQuery = false);

    /**
     * @param string $table
     * @return DriverQueryBuilder
     */
    public function setTable(string $table): DriverQueryBuilder;

    /**
     * @return string
     */
    public function getTable(): string;

    /**
     * @param mixed $columns
     * @return DriverQueryBuilder
     */
    public function setColumns($columns): DriverQueryBuilder;

    /**
     * @return array
     */
    public function getColumns(): array;

    public function clearColumns(): void;

    /**
     * @param mixed ...$columns
     * @return DriverQueryBuilder
     */
    public function setSelect(...$columns): DriverQueryBuilder;

    /**
     * @param Closure $callbackQuery
     * @param $as
     * @return DriverQueryBuilder
     */
    public function setSelectSub(Closure $callbackQuery, $as): DriverQueryBuilder;

    /**
     * @param $query
     * @param $as
     * @param array|null $bindings
     * @return DriverQueryBuilder
     */
    public function setSelectSubRaw($query, $as, array $bindings = null): DriverQueryBuilder;

    /**
     * @return array
     */
    public function getSelect(): array;

    public function clearSelect(): void;

    /**
     * @param $column
     * @param $alias
     * @param null $path
     * @return DriverQueryBuilder
     */
    public function setSelectJsonQuery($column, $alias, $path = null): DriverQueryBuilder;

    /**
     * @param $column
     * @param $alias
     * @param $path
     * @return DriverQueryBuilder
     */
    public function setSelectJsonValue($column, $alias, $path): DriverQueryBuilder;

    /**
     * @return DriverQueryBuilder
     */
    public function setDistinct(): DriverQueryBuilder;

    public function clearWhereQuery(): void;

    /**
     * @param $column
     * @param $value
     * @param null $operator
     * @return DriverQueryBuilder
     */
    public function setWhere($column, $value, $operator = null): DriverQueryBuilder;

    /**
     * @param $column
     * @param $value
     * @param null $operator
     * @return DriverQueryBuilder
     */
    public function setOrWhere($column, $value, $operator = null): DriverQueryBuilder;

    /**
     * @param $column
     * @return DriverQueryBuilder
     */
    public function setWhereIsNull($column): DriverQueryBuilder;

    /**
     * @param $column
     * @return DriverQueryBuilder
     */
    public function setOrWhereIsNull($column): DriverQueryBuilder;

    /**
     * @param $column
     * @return DriverQueryBuilder
     */
    public function setWhereIsNotNull($column): DriverQueryBuilder;

    /**
     * @param $column
     * @return DriverQueryBuilder
     */
    public function setOrWhereIsNotNull($column): DriverQueryBuilder;

    /**
     * @param $column
     * @param array|Closure $values
     * @return DriverQueryBuilder
     */
    public function setWhereIn($column, $values): DriverQueryBuilder;

    /**
     * @param $column
     * @param array|Closure $values
     * @return DriverQueryBuilder
     */
    public function setOrWhereIn($column, $values): DriverQueryBuilder;

    /**
     * @param $column
     * @param array|Closure $values
     * @return DriverQueryBuilder
     */
    public function setWhereNotIn($column, $values): DriverQueryBuilder;

    /**
     * @param $column
     * @param array|Closure $values
     * @return DriverQueryBuilder
     */
    public function setOrWhereNotIn($column, $values): DriverQueryBuilder;

    /**
     * @param $column
     * @param $value1
     * @param $value2
     * @return DriverQueryBuilder
     */
    public function setWhereBetween($column, $value1, $value2): DriverQueryBuilder;

    /**
     * @param $column
     * @param $value1
     * @param $value2
     * @return DriverQueryBuilder
     */
    public function setOrWhereBetween($column, $value1, $value2): DriverQueryBuilder;

    /**
     * @param $column
     * @param $value1
     * @param $value2
     * @return DriverQueryBuilder
     */
    public function setWhereNotBetween($column, $value1, $value2): DriverQueryBuilder;

    /**
     * @param $column
     * @param $value1
     * @param $value2
     * @return DriverQueryBuilder
     */
    public function setOrWhereNotBetween($column, $value1, $value2): DriverQueryBuilder;

    /**
     * @param $column
     * @return DriverQueryBuilder
     */
    public function setWhereIsJson($column): DriverQueryBuilder;

    /**
     * @param $column
     * @return DriverQueryBuilder
     */
    public function setOrWhereIsJson($column): DriverQueryBuilder;

    /**
     * @param $column
     * @return DriverQueryBuilder
     */
    public function setWhereIsNotJson($column): DriverQueryBuilder;

    /**
     * @param $column
     * @return DriverQueryBuilder
     */
    public function setOrWhereIsNotJson($column): DriverQueryBuilder;

    /**
     * @param $column
     * @param $value
     * @param null $path
     * @return DriverQueryBuilder
     */
    public function setWhereJsonValue($column, $value, $path): DriverQueryBuilder;

    /**
     * @param $column
     * @param $value
     * @param null $path
     * @return DriverQueryBuilder
     */
    public function setOrWhereJsonValue($column, $value, $path): DriverQueryBuilder;

    /**
     * @param $column
     * @param $value
     * @param null $path
     * @return DriverQueryBuilder
     */
    public function setWhereJsonContains($column, $value, $path = null): DriverQueryBuilder;

    /**
     * @param $column
     * @param $value
     * @param null $path
     * @return DriverQueryBuilder
     */
    public function setOrWhereJsonContains($column, $value, $path = null): DriverQueryBuilder;

    /**
     * @param $column
     * @param $value
     * @param null $path
     * @return DriverQueryBuilder
     */
    public function setWhereJsonNotContains($column, $value, $path = null): DriverQueryBuilder;

    /**
     * @param $column
     * @param $value
     * @param null $path
     * @return DriverQueryBuilder
     */
    public function setOrWhereJsonNotContains($column, $value, $path = null): DriverQueryBuilder;

    /**
     * @param $query
     * @param array $bindings
     * @return DriverQueryBuilder
     */
    public function setWhereRaw($query, array $bindings = null): DriverQueryBuilder;

    /**
     * @param $query
     * @param array $bindings
     * @return DriverQueryBuilder
     */
    public function setOrWhereRaw($query, array $bindings = null): DriverQueryBuilder;

    /**
     * @param Closure $callbackQuery
     * @return DriverQueryBuilder
     */
    public function setExists(Closure $callbackQuery): DriverQueryBuilder;

    /**
     * @param Closure $callbackQuery
     * @return DriverQueryBuilder
     */
    public function setOrExists(Closure $callbackQuery): DriverQueryBuilder;

    /**
     * @param Closure $callbackQuery
     * @return DriverQueryBuilder
     */
    public function setNotExists(Closure $callbackQuery): DriverQueryBuilder;

    /**
     * @param Closure $callbackQuery
     * @return DriverQueryBuilder
     */
    public function setOrNotExists(Closure $callbackQuery): DriverQueryBuilder;

    /**
     * @param $column
     * @param $operator
     * @param $value
     * @return DriverQueryBuilder
     */
    public function setHaving($column, $operator, $value): DriverQueryBuilder;

    /**
     * @param Closure $callbackQuery
     * @return DriverQueryBuilder
     */
    public function setUnion(Closure $callbackQuery): DriverQueryBuilder;

    /**
     * @param Closure $callbackQuery
     * @return DriverQueryBuilder
     */
    public function setUnionAll(Closure $callbackQuery): DriverQueryBuilder;

    /**
     * @param $table
     * @param $first
     * @param $second
     * @param string $type
     * @return mixed
     */
    public function setJoin($table, $first, $second, $type = 'inner'): DriverQueryBuilder;

    /**
     * @param $limit
     * @return mixed
     */
    public function setLimit($limit): DriverQueryBuilder;

    /**
     * @param $direction
     * @param mixed ...$column
     * @return DriverQueryBuilder
     */
    public function setOrderBy($direction, ...$column): DriverQueryBuilder;

    /**
     * @param mixed ...$groups
     * @return DriverQueryBuilder
     */
    public function setGroupBy(...$groups): DriverQueryBuilder;

    /**
     * @param int $queryType
     */
    public function setQueryType(int $queryType): void;

    /**
     * @return int
     */
    public function getQueryType(): int;

    /**
     * @param string $query
     */
    public function setQuery(string $query): void;

    /**
     * @return string
     */
    public function getQuery(): string;

    /**
     * @param array $bindings
     */
    public function setQueryBindings(array $bindings): void;

    /**
     * @return array
     */
    public function getQueryBindings(): array;

    public function buildQuery(): void;

}
