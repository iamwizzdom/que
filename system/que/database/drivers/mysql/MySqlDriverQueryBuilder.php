<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 5/3/2020
 * Time: 11:13 PM
 */

namespace que\database\drivers\mysql;


use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\database\interfaces\drivers\DriverQueryBuilder;

class MySqlDriverQueryBuilder implements DriverQueryBuilder
{
    /**
     * @var int
     */
    private int $queryType = 0;

    /**
     * @var string
     */
    private string $query = '';

    /**
     * @var array
     */
    private array $values = [];

    /**
     * @var bool
     */
    private bool $paginated = false;

    /**
     * @inheritDoc
     */
    public function buildQuery($table, $columns, $where = null, $join = null,
                               $limit = null, $order_by = null, $group_by = null): void
    {
        // TODO: Implement buildQuery() method.
        $this->setQuery(
            $this->build_sql_query($table, $columns, $where, $join,
                $limit, $order_by, $group_by, $this->getQueryType())
        );
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
    public function setQueryBindValues(array $values): void
    {
        // TODO: Implement setValues() method.
        $this->values = $values;
    }

    /**
     * @inheritDoc
     */
    public function getQueryBindValues(): array
    {
        // TODO: Implement getValues() method.
        return $this->values;
    }

    /**
     * @inheritDoc
     */
    public function setPaginated(bool $paginated)
    {
        // TODO: Implement setPaginated() method.
        $this->paginated = $paginated;
    }

    /**
     * @inheritDoc
     */
    public function isPaginated(): bool
    {
        // TODO: Implement isPaginated() method.
        return $this->paginated;
    }

    /**
     * @param $table
     * @param $columns
     * @param $where
     * @param $join
     * @param $limit
     * @param $order_by
     * @param $group_by
     * @param $query_type
     * @return string
     */
    private function build_sql_query($table, $columns, $where, $join, $limit,
                                     $order_by, $group_by, $query_type)
    {

        if ($query_type === self::SELECT && is_array($columns)) {
            if (!array_key_exists('columns', $columns)) {
                $columns = current($columns);
            } else {
                $this->values = $columns['values'] ?? [];
                $columns = $columns['columns'] ?? '';
            }
        }

        $query = ""; $where_query = ""; $join_query = "";

        $where_query_array = [];

        if (is_null($table) || empty($table)) return $query;

        if ($query_type != self::INSERT) {

            $where = $this->build_sql_where_query($where);

            if (!empty($where['AND']))
                array_push($where_query_array, $where['AND']);

            if (!empty($where['OR']))
                array_push($where_query_array, !empty($where_query_array) ? "({$where['OR']})" : $where['OR']);

            $where_query = implode(" AND ", $where_query_array);

            if ($query_type == self::SELECT || $query_type == self::COUNT
                || $query_type == self::AVG || $query_type == self::SUM) {

                $join_query = $this->build_sql_join_query($join);
            }
        }

        switch ($query_type) {
            case self::INSERT:

                $insertColumns = "";
                $insertValues = "";

                if (is_array($columns)) {
                    foreach ($columns as $column => $value) {
                        if (empty($column)) continue;
                        $insertColumns .= (empty($insertColumns) ? '' : ', ') . $column;

                        $bind = $this->getColumnBindUID(":{$column}");
                        $this->values[$bind] = $value;

                        $insertValues .= ((empty($insertValues) ? '' : ", ") . "{$bind}");
                    }
                }

                if (!empty($insertColumns) && !empty($insertValues))
                    $query = "INSERT INTO {$this->formatChainedColumns($table)} ({$insertColumns}) VALUES ({$insertValues})";

                break;
            case self::DELETE:

                $query = "DELETE FROM {$this->formatChainedColumns($table)}" . (!empty($where_query) ? " WHERE {$where_query}" : '');

                break;
            case self::SELECT:

                $query = "SELECT {$this->formatChainedColumns($columns)} FROM {$this->formatChainedColumns($table)}" .
                    (!empty($join_query) ? " {$join_query}" : '') . (!empty($where_query) ? " WHERE {$where_query}" : '');

                break;
            case self::UPDATE:

                $updateColumns = "";

                if (is_array($columns)) {

                    foreach ($columns as $column => $value) {
                        if (empty($column)) continue;

                        $bind = $this->getColumnBindUID(":{$column}");
                        $this->values[$bind] = $value;

                        $updateColumns .= ((empty($updateColumns) ? '' : ', ') . "{$this->formatChainedColumns($column)} = {$bind}");
                    }
                }

                if (!empty($updateColumns)) {
                    $query = "UPDATE {$this->formatChainedColumns($table)} SET {$updateColumns}"
                        . (!empty($where_query) ? " WHERE {$where_query}" : '');
                }

                break;
            case self::COUNT:

                $actualColumn = $this->getActualColumn($columns);
                $actualColumn = str_contains_any($actualColumn, [
                    '*', ',', 'select', 'update', 'insert', 'delete'
                ]) ? $actualColumn : "countable.{$actualColumn}";

                $query = "SELECT COUNT({$this->formatChainedColumns($actualColumn)}) as `aggregate` FROM 
                        (SELECT {$this->formatChainedColumns("{$this->getActualTable($table, true)}.{$this->getActualColumn($columns)}")} FROM {$this->formatChainedColumns($table)}" . (!empty($join_query) ? " {$join_query}" : '') .
                    (!empty($where_query) ? " WHERE {$where_query}" : '') .") AS `countable`";

                break;
            case self::AVG:

                $actualColumn = $this->getActualColumn($columns);
                $actualColumn = str_contains_any($actualColumn, [
                    '*', ',', 'select', 'update', 'insert', 'delete'
                ]) ? $actualColumn : "countable.{$actualColumn}";

                $query = "SELECT AVG({$this->formatChainedColumns($actualColumn)}) as `aggregate` FROM 
                        (SELECT {$this->formatChainedColumns("{$this->getActualTable($table, true)}.{$this->getActualColumn($columns)}")} FROM {$this->formatChainedColumns($table)}" . (!empty($join_query) ? " {$join_query}" : '') .
                    (!empty($where_query) ? " WHERE {$where_query}" : '') .") AS `countable`";

                break;
            case self::SUM:

                $actualColumn = $this->getActualColumn($columns);
                $actualColumn = str_contains_any($actualColumn, [
                    '*', ',', 'select', 'update', 'insert', 'delete'
                ]) ? $actualColumn : "countable.{$actualColumn}";

                $query = "SELECT SUM({$this->formatChainedColumns($actualColumn)}) as `aggregate` FROM 
                        (SELECT {$this->formatChainedColumns("{$this->getActualTable($table, true)}.{$this->getActualColumn($columns)}")} FROM {$this->formatChainedColumns($table)}" . (!empty($join_query) ? " {$join_query}" : '') .
                    (!empty($where_query) ? " WHERE {$where_query}" : '') .") AS `countable`";

                break;
            case self::SHOW:
                $query = "SHOW KEYS FROM `{$this->getActualTable($table)}` WHERE Key_name = 'PRIMARY'";
                break;
            default:
                throw new QueRuntimeException("Database driver query builder type '{$query_type}' is invalid",
                    "Database Driver Error", E_USER_ERROR, 0, PreviousException::getInstance(3));
                break;
        }

        if (is_string($group_by)) $query .= " GROUP BY {$this->formatChainedColumns($group_by)}";

        if (is_array($order_by))
            $query .= " ORDER BY " . $this->formatChainedColumns(key($order_by)) . " " . current($order_by);

        if (is_numeric($limit)) $query .= " LIMIT {$limit}";
        elseif (is_array($limit)) $query .= " LIMIT " . ($limit[0] <= 0 ? 0 : $limit[0]) . ', ' . ($limit[1] <= 0 ? 1 : $limit[1]);

        return str_strip_whitespaces($query);
    }

    /**
     * @param $where
     * @return array
     */
    private function build_sql_where_query($where): array
    {

        $where_and_query = "";
        $where_or_query = "";

        if (is_array($where)) {

            if (isset($where['AND'])) {

                $where_array = $where['AND'];

                foreach ($where_array as $table_column => $column_value) {

                    if (empty($table_column)) continue;

                    $where_and_query .= $this->decode_column_expression($where_and_query, 'AND', $table_column, $column_value);
                }

            }

            if (isset($where['OR'])) {

                $or_array = $where['OR'];

                foreach ($or_array as $table_column => $column_value) {

                    if (empty($table_column)) continue;

                    $where_or_query .= $this->decode_column_expression($where_or_query, 'OR', $table_column, $column_value);
                }

            }

        }

        return ['AND' => $where_and_query, 'OR' => $where_or_query];

    }

    /**
     * @param $join
     * @return string
     */
    private function build_sql_join_query($join): string
    {

        $join_query = "";

        if (is_array($join)) {

            foreach ($join as $join_type => $join_arr) {

                if (!is_array($join_arr)) continue;

                foreach ($join_arr as $join_table => $join_columns) {

                    if (!is_array($join_columns)) continue;

                    $join_on = "";

                    foreach ($join_columns as $local_key => $foreign_key) {
                        $join_on .= (empty($join_on) ? '' : ' AND ') . "{$this->formatChainedColumns($local_key)} = {$this->formatChainedColumns($foreign_key)}";
                    }

                    if ($join_type == '~') $join_type = '';

                    $join_type = strtoupper($join_type);

                    $join_query .= (empty($join_query) ? '' : ' ') . "{$join_type} JOIN {$this->formatChainedColumns($join_table)} ON {$this->formatChainedColumns($join_on)}";
                }
            }
        }

        return $join_query;

    }

    /**
     * @param string $ref
     * @param string $operator
     * @param string $table_column
     * @param $column_value
     * @return string
     */
    private function decode_column_expression(string &$ref, string $operator, string $table_column, $column_value): string
    {

        if (($isArray = is_array($column_value)) && !str_contains(strtolower($table_column), 'between')) {

            $column_values = array_values($column_value);

            $expression = "";
            foreach ($column_values as $value)
                $expression .= $this->decode_column_expression($expression, 'OR', $table_column, $value);
            return (!empty($ref) ? " {$operator} " : '') .  "({$expression})";

        }

        $col_operator = $this->getOperator($table_column) ?: '=';
        $table_column = $this->stripOperator($table_column) ?: $table_column;

        if ($isArray) {

            $binder = "";
            foreach ($column_value as $value) {
                $binder .= (empty($binder) ? '' : ' AND ') . ($bind = $this->getColumnBindUID(":{$table_column}"));
                $this->values[$bind] = $value;
            }

            $bind = $binder;

        } else {

            $bind = $this->getColumnBindUID(":{$table_column}");
            $this->values[$bind] = $column_value;
        }

        return (!empty($ref) ? " {$operator} " : '') . "{$this->formatChainedColumns($table_column)} {$col_operator} {$bind}";
    }

    /**
     * @param string $column
     * @return string
     */
    private function getColumnBindUID(string $column): string {
        $column = str_strip_spaces(preg_replace("/ as /i", '_', $column));
        $column = str_strip_spaces(preg_replace("/\./", '_', $column));
        if (!isset($this->values[$column])) return $column;
        $size = (array_size($this->values) + 1);
        for ($i = 0; $i < $size; $i++) {
            if (!isset($this->values["{$column}_{$i}"])) {
                $column = "{$column}_{$i}";
                break;
            }
        }
        return $column;
    }

    /**
     * @param string $column
     * @return string
     */
    private function formatChainedColumns(string $column)
    {
        if ($column === '*' || str_contains($column, '`') || str_contains_any($column, [
            'select', 'insert', 'update', 'delete'
            ], true)) return $column;

        if (!str_contains($column, '.') && !str_contains($column, ',') &&
            !str_contains(strtolower($column), ' as ')) {
            $column = str_strip_spaces($column);
            return "`{$column}`";
        }

        if (str_contains($column, ',')) {

            $column = explode(',', $column);

            array_callback($column, function ($value) {
                return $this->formatChainedColumns($value);
            });

            return implode(', ', $column);

        } elseif (str_contains(strtolower($column), ' as ')) {

            $column = preg_replace("/ as /i", " as ", $column);
            $column = explode('as', $column);

            array_callback($column, function ($value) {
                return $this->formatChainedColumns($value);
            });

            return implode(' AS ', $column);

        } else {

            $column = explode('.', $column);
            if (in_array('*', $column)) return '*';
            array_callback($column, function ($value) {
                $value = str_strip_spaces($value);
                return $value !== '*' ? "`{$value}`" : $value;
            });
            return implode('.', $column);
        }
    }

    /**
     * @param string $column
     * @param bool $alias
     * @return string
     */
    private function getActualTable(string $column, bool $alias = false) {
        if (!str_contains($column, 'as', true)) return $column;
        $column = preg_replace("/ as /i", " as ", $column);
        $column = explode('as', $column);
        return str_strip_spaces($column[$alias ? 1 : 0]);
    }

    private function getActualColumn(string $column) {
        if ($column === '*' || !str_contains($column, '.') || str_contains_any($column, [
                'select', 'insert', 'update', 'delete'
            ], true)) return $column;
        $column = explode('.', $column);
        return str_strip_spaces($column[1]);
    }

    /**
     * @param string $column
     * @return string
     */
    private function getOperator(string $column): string {
        $operator = '';
        if (preg_match('/\[(.*?)\]/', $column, $matches))
            $operator = trim($matches[1], '?');
        return $operator;
    }

    /**
     * @param string $column
     * @return string
     */
    private function stripOperator (string $column): string {
        return preg_replace('/\[(.*?)\]/', "", $column) ?: $column;
    }
}