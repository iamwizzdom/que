<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 7/24/2020
 * Time: 11:02 AM
 */

namespace que\database\interfaces;

use Closure;
use que\database\interfaces\drivers\Driver;
use que\database\interfaces\drivers\DriverQueryBuilder;
use que\database\DB;
use que\database\QueryResponse;

interface Builder
{
    /**
     * Builder constructor.
     * @param Driver $driver
     * @param DriverQueryBuilder $builder
     * @param DB $query
     */
    public function __construct(Driver $driver, DriverQueryBuilder $builder, DB $query);

    /**
     * @param string $table
     * @return Builder
     */
    public function table(string $table): Builder;

    /**
     * @param mixed ...$columns
     * @return Builder
     */
    public function columns($columns): Builder;

    /**
     * @param mixed ...$columns
     * @return Builder
     */
    public function select(...$columns): Builder;

    /**
     * @param Closure $callbackQuery
     * @param $as
     * @return Builder
     */
    public function selectSub(Closure $callbackQuery, $as): Builder;

    /**
     * @param $query
     * @param $as
     * @param array|null $bindings
     * @return Builder
     */
    public function selectSubRaw($query, $as, array $bindings = null): Builder;

    /**
     * @param $column
     * @param $alias
     * @param null $path
     * @return Builder
     */
    public function selectJsonQuery($column, $alias, $path = null): Builder;

    /**
     * @param $column
     * @param $alias
     * @param $path
     * @return Builder
     */
    public function selectJsonValue($column, $alias, $path): Builder;

    /**
     * @return Builder
     */
    public function distinct(): Builder;

    /**
     * @param $value
     * @param Closure $callback
     * @param Closure|null $default
     * @return Builder
     */
    public function when($value, Closure $callback, Closure $default = null): Builder;

    /**
     * @param $value
     * @param Closure $callback
     * @param Closure|null $default
     * @return Builder
     */
    public function whenNot($value, Closure $callback, Closure $default = null): Builder;

    /**
     * @param $column
     * @param $value
     * @param null $operator
     * @return Builder
     */
    public function where($column, $value, $operator = null): Builder;

    /**
     * @param $column
     * @param $value
     * @param null $operator
     * @return Builder
     */
    public function orWhere($column, $value, $operator = null): Builder;

    /**
     * @param $column
     * @return Builder
     */
    public function whereIsNull($column): Builder;

    /**
     * @param $column
     * @return Builder
     */
    public function orWhereIsNull($column): Builder;

    /**
     * @param $column
     * @return Builder
     */
    public function whereIsNotNull($column): Builder;

    /**
     * @param $column
     * @return Builder
     */
    public function orWhereIsNotNull($column): Builder;

    /**
     * @param $column
     * @param array|Closure $values
     * @return Builder
     */
    public function whereIn($column, $values): Builder;

    /**
     * @param $column
     * @param array|Closure $values
     * @return Builder
     */
    public function orWhereIn($column, $values): Builder;

    /**
     * @param $column
     * @param array|Closure $values
     * @return Builder
     */
    public function whereNotIn($column, $values): Builder;

    /**
     * @param $column
     * @param array|Closure $values
     * @return Builder
     */
    public function orWhereNotIn($column, $values): Builder;

    /**
     * @param $column
     * @param $value1
     * @param $value2
     * @return Builder
     */
    public function whereBetween($column, $value1, $value2): Builder;

    /**
     * @param $column
     * @param $value1
     * @param $value2
     * @return Builder
     */
    public function orWhereBetween($column, $value1, $value2): Builder;

    /**
     * @param $column
     * @param $value1
     * @param $value2
     * @return Builder
     */
    public function whereNotBetween($column, $value1, $value2): Builder;

    /**
     * @param $column
     * @param $value1
     * @param $value2
     * @return Builder
     */
    public function orWhereNotBetween($column, $value1, $value2): Builder;

    /**
     * @param $column
     * @return Builder
     */
    public function whereIsJson($column): Builder;

    /**
     * @param $column
     * @return Builder
     */
    public function orWhereIsJson($column): Builder;

    /**
     * @param $column
     * @return Builder
     */
    public function whereIsNotJson($column): Builder;

    /**
     * @param $column
     * @return Builder
     */
    public function orWhereIsNotJson($column): Builder;

    /**
     * @param $column
     * @param $value
     * @param null $path
     * @return Builder
     */
    public function whereJsonValue($column, $value, $path): Builder;

    /**
     * @param $column
     * @param $value
     * @param null $path
     * @return Builder
     */
    public function orWhereJsonValue($column, $value, $path): Builder;

    /**
     * @param $column
     * @param $value
     * @param null $path
     * @return Builder
     */
    public function whereJsonContains($column, $value, $path = null): Builder;

    /**
     * @param $column
     * @param $value
     * @param null $path
     * @return Builder
     */
    public function orWhereJsonContains($column, $value, $path = null): Builder;

    /**
     * @param $column
     * @param $value
     * @param null $path
     * @return Builder
     */
    public function whereJsonNotContains($column, $value, $path = null): Builder;

    /**
     * @param $column
     * @param $value
     * @param null $path
     * @return Builder
     */
    public function orWhereJsonNotContains($column, $value, $path = null): Builder;

    /**
     * @param $query
     * @param array $bindings
     * @return Builder
     */
    public function whereRaw($query, array $bindings = null): Builder;

    /**
     * @param $query
     * @param array $bindings
     * @return Builder
     */
    public function orWhereRaw($query, array $bindings = null): Builder;

    /**
     * @param Closure $callbackQuery
     * @return Builder
     */
    public function exists(Closure $callbackQuery): Builder;

    /**
     * @param Closure $callbackQuery
     * @return Builder
     */
    public function orExists(Closure $callbackQuery): Builder;

    /**
     * @param Closure $callbackQuery
     * @return Builder
     */
    public function notExists(Closure $callbackQuery): Builder;

    /**
     * @param Closure $callbackQuery
     * @return Builder
     */
    public function orNotExists(Closure $callbackQuery): Builder;

    /**
     * @param $column
     * @param $operator
     * @param $value
     * @return Builder
     */
    public function having($column, $operator, $value): Builder;

    /**
     * @param Closure $callbackQuery
     * @return Builder
     */
    public function union(Closure $callbackQuery): Builder;

    /**
     * @param Closure $callbackQuery
     * @return Builder
     */
    public function unionAll(Closure $callbackQuery): Builder;

    /**
     * @param $table
     * @param $first
     * @param $second
     * @param string $type
     * @return mixed
     */
    public function join($table, $first, $second, $type = 'inner'): Builder;

    /**
     * @param $limit
     * @return mixed
     */
    public function limit($limit): Builder;

    /**
     * @param $direction
     * @param mixed ...$column
     * @return Builder
     */
    public function orderBy($direction, ...$column): Builder;

    /**
     * @param mixed ...$groups
     * @return Builder
     */
    public function groupBy(...$groups): Builder;

    /**
     * @param int $perPage
     * @param string $tag
     * @param string $pageName
     * @param int $page
     * @return QueryResponse
     */
    public function paginate(int $perPage = DEFAULT_PAGINATION_PER_PAGE, string $tag = "default",
                             string $pageName = 'page', int $page = 0): QueryResponse;

    /**
     * @param int $queryType
     * @return mixed
     */
    public function setQueryType(int $queryType);

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

    /**
     * @return QueryResponse
     */
    public function exec(): QueryResponse;
}
