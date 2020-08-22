<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 5/3/2020
 * Time: 11:13 PM
 */

namespace que\database\drivers\mysql;


use Closure;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\database\interfaces\drivers\Driver;
use que\database\interfaces\drivers\DriverQueryBuilder;
use que\database\DB;
use que\database\QueryBuilder;
use que\http\HTTP;

class MySqlDriverQueryBuilder implements DriverQueryBuilder
{

    /**
     * @var Driver
     */
    private Driver $driver;

    /**
     * @var array
     */
    private array $bindings = [];

    /**
     * @var int
     */
    private int $queryType = 0;

    /**
     * @var string
     */
    private string $table = '';

    /**
     * @var bool
     */
    private bool $distinct = false;

    /**
     * @var array
     */
    private array $columns = [];

    /**
     * @var array
     */
    private array $select = [];

    /**
     * @var array
     */
    private array $where = [];

    private array $having = [];

    /**
     * @var array
     */
    private array $join = [];

    /**
     * @var array
     */
    private array $union;

    /**
     * @var mixed
     */
    private $limit;

    /**
     * @var array
     */
    private array $orderBy = [];

    /**
     * @var mixed
     */
    private $groupBy;

    /**
     * @var string
     */
    private string $query = '';


    /**
     * @inheritDoc
     */
    public function __construct(Driver $driver, array $bindings = [], bool $isSubQuery = false)
    {
        $this->driver = $driver;
        $this->bindings = $bindings;
    }

    /**
     * @inheritDoc
     */
    public function setTable(string $table): DriverQueryBuilder
    {
        // TODO: Implement setTable() method.
        $this->table = $table;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTable(): string
    {
        // TODO: Implement getTable() method.
        return $this->table;
    }

    public function setColumns($columns): DriverQueryBuilder
    {
        // TODO: Implement setInsertColumns() method.
        $this->columns = $columns;
        return $this;
    }

    public function getColumns(): array
    {
        // TODO: Implement getColumns() method.
        return $this->columns;
    }

    public function clearColumns(): void
    {
        // TODO: Implement clearColumns() method.
        $this->columns = [];
    }


    public function setSelect(...$columns): DriverQueryBuilder
    {
        // TODO: Implement setSelect() method.
        array_callback($columns, function ($column) {
            return [
                'type' => 'normal',
                'column' => $column
            ];
        });
        $this->select = array_merge($this->select, $columns);
        return $this;
    }

    public function getSelect(): array
    {
        // TODO: Implement getSelect() method.
        return array_map(function ($column) {
            $type = $column['type'] ?? '';
            return $column[$type == 'normal' ? 'column' : 'alias'] ?? '';
        }, $this->select);
    }

    public function clearSelect(): void
    {
        // TODO: Implement clearSelect() method.
        $this->select = [];
    }

    public function setSelectSub(Closure $callback, $as): DriverQueryBuilder
    {
        // TODO: Implement setSelectSub() method.
        $this->select[] = [
            'type' => 'normal',
            'column' => [$callback, $as]
        ];
        return $this;
    }

    public function setSelectSubRaw($query, $as, array $bindings = null): DriverQueryBuilder
    {
        // TODO: Implement setSelectSubRaw() method.
        $this->select[] = [
            'type' => 'raw',
            'column' => $query,
            'alias' => $as,
            'bindings' => $bindings
        ];
        return $this;
    }


    public function setSelectJsonQuery($column, $alias, $path = null): DriverQueryBuilder
    {
        // TODO: Implement setSelectJsonQuery() method.
        $this->select[] = [
            'type' => 'json_query',
            'column' => $column,
            'alias' => $alias,
            'path' => $path
        ];
        return $this;
    }

    public function setSelectJsonValue($column, $alias, $path): DriverQueryBuilder
    {
        // TODO: Implement setSelectJsonValue() method.
        $this->select[] = [
            'type' => 'json_value',
            'column' => $column,
            'alias' => $alias,
            'path' => $path
        ];
        return $this;
    }

    public function setDistinct(): DriverQueryBuilder
    {
        // TODO: Implement setDistinct() method.
        $this->distinct = true;
        return $this;
    }

    public function clearWhereQuery(): void
    {
        // TODO: Implement clearWhereQuery() method.
        $this->where = [];
    }

    /**
     * @inheritDoc
     */
    public function setWhere($column, $value, $operator = null): DriverQueryBuilder
    {
        // TODO: Implement setWhere() method.
        $this->where[] = [
            'type' => 'and',
            'column' => $column,
            'value' => $value,
            'operator' => $operator ?: '='
        ];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOrWhere($column, $value, $operator = null): DriverQueryBuilder
    {
        // TODO: Implement setOrWhere() method.
        $this->where[] = [
            'type' => 'or',
            'column' => $column,
            'value' => $value,
            'operator' => $operator ?: '='
        ];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setWhereIsNull($column): DriverQueryBuilder
    {
        // TODO: Implement setWhereIsNull() method.
        $this->where[] = [
            'type' => 'and',
            'column' => $column,
            'value' => null,
            'operator' => 'IS'
        ];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOrWhereIsNull($column): DriverQueryBuilder
    {
        // TODO: Implement setOrWhereIsNull() method.
        $this->where[] = [
            'type' => 'or',
            'column' => $column,
            'value' => null,
            'operator' => 'IS'
        ];
        return $this;
    }

    public function setWhereIsNotNull($column): DriverQueryBuilder
    {
        // TODO: Implement setWhereIsNotNull() method.
        $this->where[] = [
            'type' => 'and',
            'column' => $column,
            'value' => null,
            'operator' => 'IS NOT'
        ];
        return $this;
    }

    public function setOrWhereIsNotNull($column): DriverQueryBuilder
    {
        // TODO: Implement setOrWhereIsNotNull() method.
        $this->where[] = [
            'type' => 'or',
            'column' => $column,
            'value' => null,
            'operator' => 'IS NOT'
        ];
        return $this;
    }


    public function setWhereIn($column, $values): DriverQueryBuilder
    {
        // TODO: Implement setWhereIn() method.
        $this->where[] = [
            'type' => 'and',
            'column' => $column,
            'value' => $values,
            'operator' => 'IN'
        ];
        return $this;
    }

    public function setOrWhereIn($column, $values): DriverQueryBuilder
    {
        // TODO: Implement setOrWhereIn() method.
        $this->where[] = [
            'type' => 'or',
            'column' => $column,
            'value' => $values,
            'operator' => 'IN'
        ];
        return $this;
    }

    public function setWhereNotIn($column, $values): DriverQueryBuilder
    {
        // TODO: Implement setWhereNotIn() method.
        $this->where[] = [
            'type' => 'and',
            'column' => $column,
            'value' => $values,
            'operator' => 'NOT IN'
        ];
        return $this;
    }

    public function setOrWhereNotIn($column, $values): DriverQueryBuilder
    {
        // TODO: Implement setOrWhereNotIn() method.
        $this->where[] = [
            'type' => 'or',
            'column' => $column,
            'value' => $values,
            'operator' => 'NOT IN'
        ];
        return $this;
    }

    public function setWhereBetween($column, $value1, $value2): DriverQueryBuilder
    {
        // TODO: Implement setWhereBetween() method.
        $this->where[] = [
            'type' => 'and',
            'column' => $column,
            'value' => [$value1, $value2],
            'operator' => 'BETWEEN'
        ];
        return $this;
    }

    public function setOrWhereBetween($column, $value1, $value2): DriverQueryBuilder
    {
        // TODO: Implement setOrWhereBetween() method.
        $this->where[] = [
            'type' => 'or',
            'column' => $column,
            'value' => [$value1, $value2],
            'operator' => 'BETWEEN'
        ];
        return $this;
    }

    public function setWhereNotBetween($column, $value1, $value2): DriverQueryBuilder
    {
        // TODO: Implement setWhereNotBetween() method.
        $this->where[] = [
            'type' => 'and',
            'column' => $column,
            'value' => [$value1, $value2],
            'operator' => 'NOT BETWEEN'
        ];
        return $this;
    }

    public function setOrWhereNotBetween($column, $value1, $value2): DriverQueryBuilder
    {
        // TODO: Implement setOrWhereNotBetween() method.
        $this->where[] = [
            'type' => 'or',
            'column' => $column,
            'value' => [$value1, $value2],
            'operator' => 'NOT BETWEEN'
        ];
        return $this;
    }

    public function setWhereIsJson($column): DriverQueryBuilder
    {
        // TODO: Implement setWhereIsJson() method.
        $this->where[] = [
            'type' => 'json_valid_and',
            'column' => $column
        ];
        return $this;
    }

    public function setOrWhereIsJson($column): DriverQueryBuilder
    {
        // TODO: Implement setOrWhereIsJson() method.
        $this->where[] = [
            'type' => 'json_valid_or',
            'column' => $column
        ];
        return $this;
    }

    public function setWhereIsNotJson($column): DriverQueryBuilder
    {
        // TODO: Implement setWhereIsJson() method.
        $this->where[] = [
            'type' => 'json_valid_not_and',
            'column' => $column
        ];
        return $this;
    }

    public function setOrWhereIsNotJson($column): DriverQueryBuilder
    {
        // TODO: Implement setOrWhereIsJson() method.
        $this->where[] = [
            'type' => 'json_valid_not_or',
            'column' => $column
        ];
        return $this;
    }

    public function setWhereJsonValue($column, $value, $path): DriverQueryBuilder
    {
        // TODO: Implement setWhereJson() method.
        $this->where[] = [
            'type' => 'json_value_and',
            'column' => $column,
            'value' => $value,
            'path' => !str_starts_with($path, '$.') ? "$.{$path}" : $path
        ];
        return $this;
    }

    public function setOrWhereJsonValue($column, $value, $path): DriverQueryBuilder
    {
        // TODO: Implement setOrWhereJson() method.
        $this->where[] = [
            'type' => 'json_value_or',
            'column' => $column,
            'value' => $value,
            'path' => !str_starts_with($path, '$.') ? "$.{$path}" : $path
        ];
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function setWhereJsonContains($column, $value, $path = null): DriverQueryBuilder
    {
        // TODO: Implement setWhereJsonContains() method.
        $this->where[] = [
            'type' => 'json_contains_and',
            'column' => $column,
            'value' => $value,
            'path' => $path ? !str_starts_with($path, '$.') ? "$.{$path}" : $path : '$'
        ];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOrWhereJsonContains($column, $value, $path = null): DriverQueryBuilder
    {
        // TODO: Implement setOrWhereJsonContains() method.
        $this->where[] = [
            'type' => 'json_contains_or',
            'column' => $column,
            'value' => $value,
            'path' => $path ? !str_starts_with($path, '$.') ? "$.{$path}" : $path : '$'
        ];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setWhereJsonNotContains($column, $value, $path = null): DriverQueryBuilder
    {
        // TODO: Implement setWhereJsonNotContains() method.
        $this->where[] = [
            'type' => 'json_contains_not_and',
            'column' => $column,
            'value' => $value,
            'path' => $path ? !str_starts_with($path, '$.') ? "$.{$path}" : $path : '$'
        ];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOrWhereJsonNotContains($column, $value, $path = null): DriverQueryBuilder
    {
        // TODO: Implement setOrWhereJsonNotContains() method.
        $this->where[] = [
            'type' => 'json_contains_not_or',
            'column' => $column,
            'value' => $value,
            'path' => $path ? !str_starts_with($path, '$.') ? "$.{$path}" : $path : '$'
        ];
        return $this;
    }

    public function setWhereRaw($query, array $bindings = null): DriverQueryBuilder
    {
        // TODO: Implement setWhereRaw() method.
        $this->where[] = [
            'type' => 'raw_and',
            'column' => $query,
            'bindings' => $bindings
        ];
        return $this;
    }

    public function setOrWhereRaw($query, array $bindings = null): DriverQueryBuilder
    {
        // TODO: Implement setOrWhereRaw() method.
        $this->where[] = [
            'type' => 'raw_or',
            'column' => $query,
            'bindings' => $bindings
        ];
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function setExists(Closure $callback): DriverQueryBuilder
    {
        // TODO: Implement setWhere() method.
        $this->where[] = [
            'type' => 'exists_and',
            'column' => $callback
        ];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOrExists(Closure $callback): DriverQueryBuilder
    {
        // TODO: Implement setOrExists() method.
        $this->where[] = [
            'type' => 'exists_or',
            'column' => $callback
        ];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setNotExists(Closure $callback): DriverQueryBuilder
    {
        // TODO: Implement setNotExists() method.
        $this->where[] = [
            'type' => 'exists_not_and',
            'column' => $callback
        ];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOrNotExists(Closure $callback): DriverQueryBuilder
    {
        // TODO: Implement setOrNotExists() method.
        $this->where[] = [
            'type' => 'exists_not_or',
            'column' => $callback
        ];
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function setHaving($column, $operator, $value): DriverQueryBuilder
    {
        // TODO: Implement setHaving() method.
        $this->having[] = [
            'column' => $column,
            'value' => $value,
            'operator' => $operator
        ];
        return $this;
    }

    public function setUnion(Closure $callback): DriverQueryBuilder
    {
        // TODO: Implement setUnion() method.
        $this->union = [
            'query' => $callback,
            'type' => 'distinct'
        ];
        return $this;
    }

    public function setUnionAll(Closure $callback): DriverQueryBuilder
    {
        // TODO: Implement setUnionAll() method.
        $this->union = [
            'query' => $callback,
            'type' => 'duplicate'
        ];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setJoin($table, $first, $second, $type = 'inner'): DriverQueryBuilder
    {
        // TODO: Implement setJoin() method.
        $type = strtoupper($type);
        if ($type === 'FULL') $type = 'FULL OUTER';
        switch ($type) {
            case '~':
            case 'LEFT':
            case 'RIGHT':
            case 'INNER':
            case 'FULL OUTER':
                $this->join[] = [
                    'table' => $table,
                    'first' => $first,
                    'second' => $second,
                    'type' => $type
                ];
                break;
            default:
                throw new QueRuntimeException("Invalid SQL join type [::{$type}]", "Database Driver Error",
                    E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(4));

        }
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function setLimit($limit): DriverQueryBuilder
    {
        // TODO: Implement setLimit() method.
        $this->limit = $limit;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOrderBy($direction, ...$column): DriverQueryBuilder
    {
        // TODO: Implement setOrderBy() method.
        $this->orderBy = [$direction => $column];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setGroupBy(...$groups): DriverQueryBuilder
    {
        // TODO: Implement setGroupBy() method.
        $this->groupBy = $groups;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setQueryType(int $queryType): void
    {
        // TODO: Implement setQueryType() method.
        $this->queryType = $queryType;
    }

    /**
     * @inheritDoc
     */
    public function getQueryType(): int
    {
        // TODO: Implement getQueryType() method.
        return $this->queryType;
    }

    /**
     * @inheritDoc
     */
    public function setQuery(string $query): void
    {
        // TODO: Implement setQuery() method.
        $this->query = $query;
    }

    /**
     * @inheritDoc
     */
    public function getQuery(): string
    {
        // TODO: Implement getQuery() method.
        return $this->query;
    }

    /**
     * @inheritDoc
     */
    public function setQueryBindings(array $bindings): void
    {
        // TODO: Implement setQueryBindValues() method.
        $this->bindings = $bindings;
    }

    /**
     * @inheritDoc
     */
    public function getQueryBindings(): array
    {
        // TODO: Implement getQueryBindValues() method.
        return $this->bindings;
    }

    public function buildQuery(): void
    {
        // TODO: Implement buildQuery() method.
        if (empty($this->table)) throw new QueRuntimeException(
            "No database table was specified for the current transaction", "Database Error",
            E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(3));

        $this->setQuery(
            $this->build_sql_query()
        );
    }

    /**
     * @return string
     */
    private function build_sql_query(): string
    {

        $query = "";
        $select_columns = "";
        $where_query = "";
        $join_query = "";

        if (is_null($this->table) || empty($this->table)) return $query;

        if ($this->queryType != self::SHOW && $this->queryType != self::INSERT) {

            if ($this->queryType == self::SELECT) $select_columns = $this->build_select_query();

            $where = $this->build_sql_where_query();

            if (!empty($where['AND'])) {
                $where['AND'] = array_map(function ($where) {
                    return str_char_count($where, " OR ") > 0 ? "({$where})" : $where;
                }, $where['AND']);
                $where_query = implode(" AND ", $where['AND']);
            }

            if (!empty($where['OR'])) {
                if (!empty($where_query)) {
                    $where['OR'] = array_map(function ($where) {
                        return str_char_count($where, " OR ") > 0 ? "({$where})" : $where;
                    }, $where['OR']);
                }
                $where['OR'] = count($where['OR']) > 1 ? "(" . implode(" OR ", $where['OR']) . ")" : implode(" OR ", $where['OR']);
                $where_query .= !empty($where_query) ? " OR {$where['OR']}" : " {$where['OR']}";
            }

            if ($this->queryType == self::SELECT || $this->queryType == self::COUNT
                || $this->queryType == self::AVG || $this->queryType == self::SUM) {

                $join_query = $this->build_sql_join_query();
            }
        }

        switch ($this->queryType) {
            case self::INSERT:

                $insertColumns = "";
                $insertValues = "";

                foreach ($this->columns as $column => $value) {

                    if (empty($column)) continue;

                    $insertColumns .= (empty($insertColumns) ? '' : ', ') . $this->formatColumn($column);

                    $insertValues .= ((empty($insertValues) ? '' : ", ") . "{$this->addBinding("{$column}", $value)}");
                }

                if (!empty($insertColumns) && !empty($insertValues))
                    $query = "INSERT INTO {$this->formatColumn($this->table)} ({$insertColumns}) VALUES ({$insertValues})";


                break;
            case self::DELETE:

                $query = "DELETE FROM {$this->formatColumn($this->table)}" . (!empty($where_query) ? " WHERE {$where_query}" : '');

                break;
            case self::SELECT:

                $distinct = "";
                $union = "";
                if ($this->distinct) $distinct = "DISTINCT";

                if (!empty($this->union)) {

                    $driverBuilder = new MySqlDriverQueryBuilder($this->driver, $this->bindings);
                    $driverBuilder->setQueryType($this->queryType);
                    $builder = new QueryBuilder($this->driver, $driverBuilder, DB::getInstance());
                    $builder->table($this->table);
                    $this->union['query']($builder);
                    $driverBuilder->buildQuery();
                    $this->bindings = $builder->getQueryBindings();

                    $union = ($this->union['type'] == 'distinct' ? " UNION {$builder->getQuery()}" : " UNION ALL {$builder->getQuery()}");
                }

                $query = "SELECT {$distinct} {$select_columns} FROM {$this->formatColumn($this->table)}" .
                    (!empty($join_query) ? " {$join_query}" : '') . (!empty($where_query) ? " WHERE {$where_query}" : '') . $union;

                break;
            case self::UPDATE:

                $updateColumns = "";

                foreach ($this->columns as $column => $value) {

                    if (empty($column)) continue;

                    $updateColumns .= ((empty($updateColumns) ? '' : ', ') .
                        "{$this->formatColumn($column)} = {$this->addBinding("{$column}", $value)}");
                }

                if (!empty($updateColumns)) {
                    $query = "UPDATE {$this->formatColumn($this->table)} SET {$updateColumns}"
                        . (!empty($where_query) ? " WHERE {$where_query}" : '');
                }

                break;
            case self::COUNT:

                $actualColumn = $this->getActualColumn(implode(', ', array_map(function ($column) {
                    if (is_array($column['column'])) {
                        if (is_string($col = current($column['column']))) return $col;
                        else return key($column['column']);
                    }
                    return $column['column'];
                }, $this->columns)));

                $this->setQueryType(self::SELECT);
                $this->buildQuery();
                $this->setQueryType(self::COUNT);

                $query = "SELECT COUNT({$this->formatColumn("`countable`.{$actualColumn}")}) as `aggregate` FROM ({$this->getQuery()}) AS `countable`";

                break;
            case self::AVG:

                $actualColumn = $this->getActualColumn(implode(', ', array_map(function ($column) {
                    if (is_array($column['column'])) {
                        if (is_string($col = current($column['column']))) return $col;
                        else return key($column['column']);
                    }
                    return $column['column'];
                }, $this->columns)));

                $this->setQueryType(self::SELECT);
                $this->buildQuery();
                $this->setQueryType(self::AVG);

                $query = "SELECT AVG({$this->formatColumn("`countable`.{$actualColumn}")}) as `aggregate` FROM ({$this->getQuery()}) AS `countable`";

                break;
            case self::SUM:

                $actualColumn = $this->getActualColumn(implode(', ', array_map(function ($column) {
                    if (is_array($column['column'])) {
                        if (is_string($col = current($column['column']))) return $col;
                        else return key($column['column']);
                    }
                    return $column['column'];
                }, $this->columns)));

                $this->setQueryType(self::SELECT);
                $this->buildQuery();
                $this->setQueryType(self::SUM);

                $query = "SELECT SUM({$this->formatColumn("`countable`.{$actualColumn}")}) as `aggregate` FROM ({$this->getQuery()}) AS `countable`";

                break;
            case self::SHOW:
                $query = "SHOW KEYS FROM {$this->getActualTable($this->table)} WHERE Key_name = 'PRIMARY'";
                break;
            default:
                throw new QueRuntimeException("Database driver query builder type '{$this->queryType}' is invalid",
                    "Database Driver Error", E_USER_ERROR, 0, PreviousException::getInstance(3));
                break;
        }

        if ($this->queryType != self::SHOW && !empty($this->groupBy)) {
            $this->groupBy = (array)$this->groupBy;
            $this->groupBy = implode(", ", $this->groupBy);
            $query .= " GROUP BY {$this->groupBy}";
        }

        if ($this->queryType != self::SHOW && !empty($having = $this->build_sql_having())) $query .= " HAVING {$having}";

        if ($this->queryType != self::SHOW && !empty($this->orderBy)) {
            $query .= " ORDER BY " . $this->formatColumn(implode(', ',
                    current($this->orderBy))) . " " . key($this->orderBy);
        }

        if ($this->queryType != self::SHOW) {

            if (is_numeric($this->limit)) $query .= " LIMIT {$this->addBinding('limit', $this->limit)}";
            elseif (is_array($this->limit)) $query .= " LIMIT {$this->addBinding('limit', ($this->limit[0] <= 0 ? 0 : $this->limit[0]))}, {$this->addBinding('offset', ($this->limit[1] <= 0 ? 1 : $this->limit[1]))}";
        }

        return str_strip_whitespaces($query);
    }

    private function build_select_query()
    {

        $select = '';

        foreach ($this->select as $column) {

            switch ($column['type'] ?? '') {
                case 'normal':

                    $column = $column['column'];

                    if (is_array($column)) {

                        $alias = current($column);
                        $column = key($column);

                        if (is_callable($alias)) {

                            $driverBuilder = new MySqlDriverQueryBuilder($this->driver, $this->bindings);
                            $driverBuilder->setQueryType($this->queryType);
                            $builder = new QueryBuilder($this->driver, $driverBuilder, DB::getInstance());
                            $builder->table($this->table);
                            $alias($builder);
                            $driverBuilder->buildQuery();
                            $this->bindings = $builder->getQueryBindings();
                            $select .= (!empty($select) ? ', ' : '') . "({$builder->getQuery()}) AS {$this->formatColumn($column)}";

                        } elseif (is_numeric($column)) $select .= (!empty($select) ? ', ' : '') . $this->formatColumn($alias);
                        else $select .= (!empty($select) ? ', ' : '') . $this->formatColumn("{$column} AS {$alias}");

                    } else $select .= (!empty($select) ? ', ' : '') . $this->formatColumn($column);

                    break;
                case 'raw':

                    $binders = $this->addBindings($column['bindings'] ?: []);
                    foreach ($binders as $bind) $column['column'] = str_replace_first("?", $bind, $column['column']);
                    $select .= (!empty($select) ? ', ' : '') . "{$column['column']} as {$column['alias']}";
                    break;
                case 'json_query':
                case 'json_value':

                    $column['type'] = strtoupper($column['type']);

                    if (!array_find($this->where, function ($where) use ($column) {
                        return $where['type'] === 'json_valid_and' && $where['column'] === $column['column'];
                    })) $this->setWhereIsJson($column['column']);

                    $select .= (!empty($select) ? ', ' : '') .
                        "{$column['type']}({$column['column']}, '{$column['path']}') AS {$this->formatColumn($column['alias'])}";
                    break;
                default:
                    break;
            }
        }

        return $select;
    }

    /**
     * @return string
     */
    private function build_sql_having(): string
    {

        $having = '';

        foreach ($this->having as $expression) {

            switch (strtoupper($expression['operator'])) {
                case 'BETWEEN':

                    $btw1 = $this->addBinding("{$expression['column']}", $expression['value'][0]);
                    $btw2 = $this->addBinding("{$expression['column']}", $expression['value'][2]);
                    $binder = "{$btw1} AND {$btw2}";

                    $having .= (!empty($having) ? " AND " : '') .
                        "{$expression['column']} {$expression['operator']} {$binder}";

                    break;
                case 'IN':

                    if (is_array($expression['value'])) {

                        $binders = [];
                        foreach ($expression['value'] as $value) {
                            $binders[] = $this->addBinding("{$expression['column']}", $value);
                        }

                        $having .= (!empty($having) ? " AND " : '') .
                            "{$expression['column']} {$expression['operator']} (" . implode(", ", $binders) . ")";

                    } elseif (is_callable($expression['value'])) {

                        $driverBuilder = new MySqlDriverQueryBuilder($this->driver, $this->bindings);
                        $driverBuilder->setQueryType($this->queryType);
                        $builder = new QueryBuilder($this->driver, $driverBuilder, DB::getInstance());
                        $builder->table($this->table);
                        $expression['value']($builder);
                        $driverBuilder->buildQuery();
                        $this->bindings = $builder->getQueryBindings();

                        $having .= (!empty($having) ? " AND " : '') .
                            "{$expression['column']} {$expression['operator']} ({$builder->getQuery()})";
                    }

                    break;
                default:

                    $binder = $this->addBinding("{$expression['column']}", $expression['value']);

                    $having .= (!empty($having) ? " AND " : '') .
                        "{$expression['column']} {$expression['operator']} {$binder}";
                    break;
            }
        }

        return $having;
    }

    /**
     * @return array
     */
    private function build_sql_where_query(): array
    {
        $where = [
            'OR' => [],
            'AND' => []
        ];


        foreach ($this->where as $expression) {

            switch ($expression['type'] ?? '') {
                case 'and':
                    $where['AND'][] = $this->decode_where_query_expression($where['AND'], $expression);
                    break;
                case 'or':
                    $where['OR'][] = $this->decode_where_query_expression($where['OR'], $expression);
                    break;
                case 'raw_and':
                    $binders = $this->addBindings($expression['bindings'] ?: []);
                    foreach ($binders as $bind) $expression['column'] = str_replace_first("?", $bind, $expression['column']);
                    $where['AND'][] = $expression['column'];
                    break;
                case 'raw_or':
                    $binders = $this->addBindings($expression['bindings'] ?: []);
                    foreach ($binders as $bind) $expression['column'] = str_replace_first("?", $bind, $expression['column']);
                    $where['OR'][] = $expression['column'];
                    break;
                case 'exists_and':

                    $driverBuilder = new MySqlDriverQueryBuilder($this->driver, $this->bindings);
                    $driverBuilder->setQueryType($this->queryType);
                    $builder = new QueryBuilder($this->driver, $driverBuilder, DB::getInstance());
                    $builder->table($this->table);
                    $expression['column']($builder);
                    $driverBuilder->buildQuery();
                    $this->bindings = $builder->getQueryBindings();

                    $where['AND'][] = "EXISTS ({$builder->getQuery()})";
                    break;
                case 'exists_or':
                    $driverBuilder = new MySqlDriverQueryBuilder($this->driver, $this->bindings);
                    $driverBuilder->setQueryType($this->queryType);
                    $builder = new QueryBuilder($this->driver, $driverBuilder, DB::getInstance());
                    $builder->table($this->table);
                    $expression['column']($builder);
                    $driverBuilder->buildQuery();
                    $this->bindings = $builder->getQueryBindings();

                    $where['OR'][] = "EXISTS ({$builder->getQuery()})";
                    break;
                case 'exists_not_and':

                    $driverBuilder = new MySqlDriverQueryBuilder($this->driver, $this->bindings);
                    $driverBuilder->setQueryType($this->queryType);
                    $builder = new QueryBuilder($this->driver, $driverBuilder, DB::getInstance());
                    $builder->table($this->table);
                    $expression['column']($builder);
                    $driverBuilder->buildQuery();
                    $this->bindings = $builder->getQueryBindings();

                    $where['AND'][] = "NOT EXISTS ({$builder->getQuery()})";
                    break;
                case 'exists_not_or':

                    $driverBuilder = new MySqlDriverQueryBuilder($this->driver, $this->bindings);
                    $driverBuilder->setQueryType($this->queryType);
                    $builder = new QueryBuilder($this->driver, $driverBuilder, DB::getInstance());
                    $builder->table($this->table);
                    $expression['column']($builder);
                    $driverBuilder->buildQuery();
                    $this->bindings = $builder->getQueryBindings();

                    $where['OR'][] = "NOT EXISTS ({$builder->getQuery()})";
                    break;
                case 'json_value_and':
                    $binder = $this->addBinding("{$expression['column']}", $expression['value']);
                    $where['AND'][] = "JSON_VALUE({$expression['column']}, '{$expression['path']}') = {$binder}";
                    break;
                case 'json_value_or':
                    $binder = $this->addBinding("{$expression['column']}", $expression['value']);
                    $where['OR'][] = "JSON_VALUE({$expression['column']}, '{$expression['path']}') = {$binder}";
                    break;
                case 'json_contains_and':
                    $binder = $this->addBinding("{$expression['column']}", $expression['value']);
                    $where['AND'][] = "JSON_CONTAINS({$expression['column']}, {$binder}, '{$expression['path']}')";
                    break;
                case 'json_contains_or':
                    $binder = $this->addBinding("{$expression['column']}", $expression['value']);
                    $where['OR'][] = "JSON_CONTAINS({$expression['column']}, {$binder}, '{$expression['path']}')";
                    break;
                case 'json_contains_not_and':
                    $binder = $this->addBinding("{$expression['column']}", $expression['value']);
                    $where['AND'][] = "NOT JSON_CONTAINS({$expression['column']}, {$binder}, '{$expression['path']}')";
                    break;
                case 'json_contains_not_or':
                    $binder = $this->addBinding("{$expression['column']}", $expression['value']);
                    $where['OR'][] = "NOT JSON_CONTAINS({$expression['column']}, {$binder}, '{$expression['path']}')";
                    break;
                case 'json_valid_and':
                    $binder = $this->addBinding("{$expression['column']}", 1);
                    $where['AND'][] = "JSON_VALID({$expression['column']}) = {$binder}";
                    break;
                case 'json_valid_or':
                    $binder = $this->addBinding("{$expression['column']}", 1);
                    $where['OR'][] = "JSON_VALID({$expression['column']}) = {$binder}";
                    break;
                case 'json_valid_not_and':
                    $binder = $this->addBinding("{$expression['column']}", 1);
                    $where['AND'][] = "NOT JSON_VALID({$expression['column']}) = {$binder}";
                    break;
                case 'json_valid_not_or':
                    $binder = $this->addBinding("{$expression['column']}", 1);
                    $where['OR'][] = "NOT JSON_VALID({$expression['column']}) = {$binder}";
                    break;
            }

        }

        return $where;
    }

    /**
     * @param $where_list
     * @param $expression
     * @return string
     */
    private function decode_where_query_expression($where_list, $expression): string
    {

        if (is_array($expression['value'])) {

            switch ($expression['operator']) {
                case 'BETWEEN':

                    $btw1 = $this->addBinding("{$expression['column']}", $expression['value'][0]);
                    $btw2 = $this->addBinding("{$expression['column']}", $expression['value'][1]);
                    $binder = "{$btw1} AND {$btw2}";

                    break;
                case 'IN':

                    $binders = [];
                    foreach ($expression['value'] as $value) {
                        $binders[] = $this->addBinding("{$expression['column']}", $value);
                    }
                    $binder = "(" . implode(", ", $binders) . ")";

                    break;
                default:
                    $ex_expression = "";
                    foreach ($expression['value'] as $value) {
                        $ex_expression .= (!empty($ex_expression) ? " OR " : '') . $this->decode_where_query_expression($ex_expression, array_merge($expression, ['value' => $value]));
                    }
                    return (!empty($where_list) ? "({$ex_expression})" : "{$ex_expression}");

            }

        } elseif (is_callable($expression['value'])) {

            $driverBuilder = new MySqlDriverQueryBuilder($this->driver, $this->bindings);
            $driverBuilder->setQueryType($this->queryType);
            $builder = new QueryBuilder($this->driver, $driverBuilder, DB::getInstance());
            $builder->table($this->table);
            $expression['value']($builder);
            $driverBuilder->buildQuery();
            $this->bindings = $builder->getQueryBindings();

            return "{$this->formatColumn($expression['column'])} {$expression['operator']} ({$builder->getQuery()})";

        } else $binder = $this->addBinding("{$expression['column']}", $expression['value']);

        return "{$this->formatColumn($expression['column'])} {$expression['operator']} {$binder}";
    }

    /**
     * @return string
     */
    private function build_sql_join_query(): string
    {
        $join_query = "";

        foreach ($this->join as $join) {

            $join_on = "";

            foreach ((array)$join['first'] as $key => $local_key) {
                $foreign_keys = (array)$join['second'];
                $join_on .= (empty($join_on) ? '' : ' AND ') . "{$this->formatColumn($local_key)} = {$this->formatColumn($foreign_keys[$key])}";
            }

            if ($join['type'] === '~') $join['type'] = '';

            $join_query .= (empty($join_query) ? '' : ' ') . " {$join['type']} JOIN {$this->formatColumn($join['table'])} ON {$join_on}";
        }

        return $join_query;
    }

    /**
     * @param string $column
     * @param $value
     * @return string
     */
    private function addBinding(string $column, $value): string
    {
        if (preg_match('/\((.*?)\)/', $column, $matches))
            $column = trim($matches[1], '?');

        $column = preg_replace("/[.,]/", '_', str_strip_spaces($column));

        if (!isset($this->bindings[":{$column}"])) {
            $this->bindings[":{$column}"] = $value;
            return ":{$column}";
        }

        $size = (array_size($this->bindings) + 1);
        for ($i = 0; $i < $size; $i++) {
            if (!isset($this->bindings[":{$i}_{$column}"])) {
                $column = ":{$i}_{$column}";
                break;
            }
        }

        $this->bindings[$column] = $value;
        return $column;
    }

    /**
     * @param array $bindings
     * @return array
     */
    private function addBindings(array $bindings) {
        $binders = [];
        foreach ($bindings as $key => $value) $binders[$key] = $this->addBinding($key, $value);
        return $binders;
    }

    /**
     * @param string $column
     * @param bool $alias
     * @return string
     */
    private function getActualTable(string $column, bool $alias = false)
    {
        $column = preg_replace("/ as /i", " as ", $column);
        if (!str_contains($column, ' as ', true)) return $this->formatColumn($column);
        $column = explode(' as ', $column);
        return $this->formatColumn($column[$alias ? 1 : 0]);
    }

    private function getActualColumn(string $column)
    {
        if (str_contains($column, ',')) return $this->getActualColumn(explode(',', $column)[0]);
        if ($column === '*' || !str_contains($column, '.')) return $this->formatColumn($column);
        $column = explode('.', $column);
        return $this->formatColumn($column[1]);
    }

    /**
     * @param string $column
     * @return string
     */
    private function formatColumn(string $column)
    {
        if ($column === '*') return $column;

        $column = preg_replace("/ as /i", " as ", $column);

        if (!str_contains($column, '.') && !str_contains($column, ',') &&
            !str_contains(strtolower($column), ' as ')) {
            $column = str_strip_spaces($column);
            return !str_contains($column, '`') ? "`{$column}`" : $column;
        }

        if (str_contains($column, ',')) {

            $column = explode(',', $column);

            array_callback($column, function ($value) {
                return $this->formatColumn($value);
            });

            return implode(', ', $column);

        } elseif (str_contains($column, ' as ')) {
            $column = explode(' as ', $column);

            array_callback($column, function ($value) {
                return $this->formatColumn($value);
            });

            return implode(' AS ', $column);

        } else {

            $column = explode('.', $column);
            array_callback($column, function ($value) {
                $value = str_strip_spaces($value);
                return $value !== '*' ? (!str_contains($value, '`') ? "`{$value}`" : $value) : $value;
            });
            return implode('.', $column);
        }
    }
}
