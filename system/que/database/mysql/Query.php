<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 10/21/2017
 * Time: 10:58 PM
 */

namespace que\database\mysql;

use que\template\Pagination;

class Query extends Connect
{
    const INSERT = 1;
    const SELECT = 2;
    const UPDATE = 3;
    const DELETE = 4;
    const COUNT = 5;
    const AVG = 6;
    const SUM = 7;

    /**
     * @var Query
     */
    private static $instance;

    /**
     * @var bool
     */
    private $paginate = false;

    /**
     * @var string
     */
    private $tag = "";

    /**
     * @var int
     */
    private $page = 0;

    /**
     * @var int
     */
    private $recordPerPage = 0;

    /**
     * Query constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    private function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

    /**
     * @return Query
     */
    public static function getInstance(): Query
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param bool $paginate
     */
    private function setPaginateStatus(bool $paginate)
    {
        $this->paginate = $paginate;
    }

    /**
     * @param string $tag
     */
    private function setTag(string $tag)
    {
        $this->tag = $tag;
    }

    /**
     * @param int $page
     */
    private function setPage(int $page)
    {
        $this->page = $page;
    }

    /**
     * @param int $recordPerPage
     */
    private function setRecordPerPage(int $recordPerPage)
    {
        $this->recordPerPage = $recordPerPage;
    }

    /**
     * @return bool
     */
    private function getPaginateStatus(): bool
    {
        return $this->paginate;
    }

    /**
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @return int
     */
    private function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    private function getRecordPerPage(): int
    {
        return $this->recordPerPage;
    }

    /**
     * @param string $tag
     * @param int $recordPerPage
     * @return Query
     */
    public function paginate(string $tag = "default", int $recordPerPage = DEFAULT_PAGINATION_RECORD_PER_PAGE): Query
    {
        $this->setPaginateStatus(true);
        $this->setRecordPerPage($recordPerPage);
        $this->setTag($tag);
        $page = http()->_get()->get('p', 1);
        $this->setPage($page);
        return $this;
    }

    /**
     * @param string $table
     * @param array $column
     * @return MySQL_Handler
     */
    public function insert(string $table, array $column): MySQL_Handler
    {
        $column = $this->filter_array($column);
        $sql = $this->build_sql_query($table, $column, null, null,
            null, null, null, self::INSERT);
        $connect = $this->connect();
        $result = $connect->query($sql);
        return new MySQL_Handler(($result === true ? $connect->insert_id : null),
            ($result === true ? true : false), $connect->error, $sql, $table);
    }

    /**
     * @param string $table
     * @param array|null $where
     * @return MySQL_Handler
     */
    public function delete(string $table, array $where = null): MySQL_Handler
    {
        $sql = $this->build_sql_query($table, null, $where, null,
            null, null, null, self::DELETE);
        $connect = $this->connect();
        $result = $connect->query($sql);
        return new MySQL_Handler(null, ($result === true ? true : false), $connect->error, $sql, $table);
    }

    /**
     * @param string $table
     * @param array|null $where
     * @param array|null $join
     * @return MySQL_Handler
     */
    public function check(string $table, array $where = null, array $join = null): MySQL_Handler
    {
        $sql = $this->build_sql_query($table, "*", $where, $join, 1,
            null, null, self::SELECT);
        $connect = $this->connect();
        $result = $connect->query($sql);
        $check = (is_object($result) && $result->num_rows > 0);
        return new MySQL_Handler(($check ? $result->num_rows : null), ($check ? true : false), $connect->error, $sql, $table);
    }

    /**
     * @param string $table
     * @param string $primaryKey
     * @param $id
     * @param string $column
     * @param array|null $join
     * @return MySQL_Handler
     */
    public function find(string $table, string $primaryKey, $id, string $column = '*', array $join = null)
    {
        return $this->select($table, $column, [
            'AND' => [
                $primaryKey => $id
            ]
        ], $join, 1);
    }

    /**
     * @param string $table
     * @param string|null $key
     * @param null $id
     * @param string $column
     * @param array|null $join
     * @param array|null $order_by
     * @param string|null $group_by
     * @return MySQL_Handler
     */
    public function findAll(string $table, string $key = null,
                            $id = null, string $column = '*', array $join = null,
                            array $order_by = null, string $group_by = null)
    {
        $where = null;

        if (!empty($key)) {
            $where = [
                'AND' => [
                    $key => $id
                ]
            ];
        }
        return $this->select($table, $column, $where, $join, null, $order_by, $group_by);
    }

    /**
     * @param string $table
     * @param string $column
     * @param array|null $where
     * @param array|null $join
     * @param null $limit
     * @param array|null $order_by
     * @param string|null $group_by
     * @return MySQL_Handler
     */
    public function select(string $table, string $column, array $where = null, array $join = null,
                           $limit = null, array $order_by = null, string $group_by = null): MySQL_Handler
    {
        if ($this->getPaginateStatus() === true) {

            $count = $this->count($table, !str_contains($column, ',') ? $column : '*',
                $where, $join, $limit, $order_by, $group_by);

            $totalPages = ceil(($totalRecord = ($count->isSuccessful() ?
                    $count->getQueryResponse() : 0)) / $this->getRecordPerPage());

            if ($totalPages && $this->getPage() > $totalPages) {
                $this->setPage($totalPages);
            }

            $limit = [(($this->getPage() - 1) * $this->getRecordPerPage()), $this->getRecordPerPage()];

            $startPage = 1;
            $endPage = ($totalPages > 10 ? 11 : $totalPages);
            $totalBatch = $this->round_up_to_nearest(($totalPages / 10), 10);
            $currentPage = $this->getPage();

            for ($i = 1; $i < $totalBatch; $i++) {
                $startPage = ($i > 1 ? (($i - 1) * 10) : $i);
                $endPage = ($i * 10);
                if ($currentPage >= $startPage && $currentPage <= $endPage) {
                    $startPage = ($startPage > 1 ? ($startPage + 1) : $startPage);
                    $endPage = ($endPage > $totalPages ? $totalPages : $endPage);
                    break;
                }
            }

            Pagination::getInstance()->paginate(
                $this->getPage(),
                $startPage,
                $endPage,
                $totalPages,
                $totalRecord,
                $this->getTag()
            );

            $this->setPaginateStatus(false);

        }

        $sql = $this->build_sql_query($table, $column, $where, $join, $limit, $order_by, $group_by, self::SELECT);

        $connect = $this->connect(); $result = $connect->query($sql);

        $output = []; $success = ($result && $result->num_rows > 0);

        if ($success)
            while ($row = $result->fetch_object())
                $output[] = $this->filter_object($row);

        return new MySQL_Handler($output, $success, $connect->error, $sql, $table);
    }

    /**
     * @param string $sql
     * @return MySQL_Handler
     */
    public function select_query(string $sql): MySQL_Handler
    {

        $connect = $this->connect();

        if ($this->getPaginateStatus() === true) {

            $sql_arr = explode(" ", $sql);
            $start = strpos_in_array($sql_arr, "SELECT", STRPOS_IN_ARRAY_OPT_ARRAY_INDEX);
            $end = strpos_in_array($sql_arr, "FROM", STRPOS_IN_ARRAY_OPT_ARRAY_INDEX);
            $column = implode(" ", array_extract($sql_arr, ($start + 1), ($end - 1)));
            $column = !str_contains($column, ",") ? $column : '*';

            $count_sql = str_replace($column, "COUNT($column) AS total", $sql);
            $result = $connect->query($count_sql);

            $count = new MySQL_Handler(($result ? $result->fetch_assoc()['total'] : null),
                ($result ? true : false), $connect->error, $count_sql, $this->get_table_name($sql));
            $totalPages = ceil(($totalRecord = ($count->isSuccessful() ? $count->getQueryResponse() : 0)) / $this->getRecordPerPage());

            if ($totalPages && $this->getPage() > $totalPages) {
                $this->setPage($totalPages);
            }

            $sql .= " LIMIT " . (($this->getPage() - 1) * $this->getRecordPerPage()) . ", " . $this->getRecordPerPage();

            $startPage = 1;
            $endPage = ($totalPages > 10 ? 11 : $totalPages);
            $totalBatch = $this->round_up_to_nearest(($totalPages / 10), 10);
            $currentPage = $this->getPage();

            for ($i = 1; $i < $totalBatch; $i++) {
                $startPage = ($i > 1 ? (($i - 1) * 10) : $i);
                $endPage = ($i * 10);
                if ($currentPage >= $startPage && $currentPage <= $endPage) {
                    $startPage = ($startPage > 1 ? ($startPage + 1) : $startPage);
                    $endPage = ($endPage > $totalPages ? $totalPages : $endPage);
                    break;
                }
            }

            Pagination::getInstance()->paginate(
                $this->getPage(),
                $startPage,
                $endPage,
                $totalPages,
                $totalRecord,
                $this->getTag()
            );

            $this->setPaginateStatus(false);

        }

        $result = $connect->query($sql);
        $output = []; $success = ($result && $result->num_rows > 0);

        if ($success)
            while ($row = $result->fetch_object())
                $output[] = $this->filter_object($row);

        return new MySQL_Handler($output, $success, $connect->error, $sql, $this->get_table_name($sql));
    }

    /**
     * @param string $sql
     * @return MySQL_Handler
     */
    public function raw_query(string $sql): MySQL_Handler
    {
        $connect = $this->connect();
        $result = $connect->query($sql);
        return new MySQL_Handler(null, ($result === true ? true : false), $connect->error, $sql, $this->get_table_name($sql));
    }

    /**
     * @param string $sql
     * @return MySQL_Handler
     */
    public function raw_sql_object(string $sql): MySQL_Handler
    {
        $connect = $this->connect();
        $result = $connect->query($sql);
        $success = ($result && $result->num_rows > 0);
        return new MySQL_Handler(($success ? $this->filter_object($result->fetch_object()) : null),
            ($success ? true : false), $connect->error, $sql, $this->get_table_name($sql));
    }

    /**
     * @param string $table
     * @param array $column
     * @param array|null $where
     * @return MySQL_Handler
     */
    public function update(string $table, array $column, array $where = null): MySQL_Handler
    {
        $column = $this->filter_array($column);
        $sql = $this->build_sql_query($table, $column, $where, null, null,
            null, null, self::UPDATE);
        $connect = $this->connect();
        $result = $connect->query($sql);
        return new MySQL_Handler(null, ($result === true ? true : false), $connect->error, $sql, $table);
    }

    /**
     * @param string $table
     * @param string $column
     * @param array|null $where
     * @param null $join
     * @param null $limit
     * @param array|null $order_by
     * @param string|null $group_by
     * @return MySQL_Handler
     */
    public function count(string $table, string $column, array $where = null, $join = null,
                          $limit = null, array $order_by = null, string $group_by = null): MySQL_Handler
    {
        $sql = $this->build_sql_query($table, $column, $where, $join, $limit,
            $order_by, $group_by, self::COUNT);
        $connect = $this->connect();
        $result = $connect->query($sql);
        $assoc = ['total' => 0];
        $check = $result && ($assoc = $result->fetch_assoc());
        return new MySQL_Handler(($check ? $result->num_rows > 1 && $result->num_rows > $assoc['total'] ?
            $result->num_rows : $assoc['total'] : 0), $check, $connect->error, $sql, $table);
    }

    /**
     * @param string $table
     * @param string $column
     * @param array|null $where
     * @param null $join
     * @param null $limit
     * @param array|null $order_by
     * @param string|null $group_by
     * @return MySQL_Handler
     */
    public function avg(string $table, string $column, array $where = null, $join = null,
                        $limit = null, array $order_by = null, string $group_by = null): MySQL_Handler
    {
        $sql = $this->build_sql_query($table, $column, $where, $join, $limit,
            $order_by, $group_by, self::AVG);
        $connect = $this->connect();
        $result = $connect->query($sql);
        $assoc = ['total' => 0];
        $check = $result && ($assoc = $result->fetch_assoc());
        return new MySQL_Handler(($check ? $assoc['total'] : 0), $check, $connect->error, $sql, $table);
    }

    /**
     * @param string $table
     * @param string|null $column
     * @param array|null $where
     * @param null $join
     * @param null $limit
     * @param array|null $order_by
     * @param string|null $group_by
     * @return MySQL_Handler
     */
    public function sum(string $table, string $column, array $where = null, $join = null,
                        $limit = null, array $order_by = null, string $group_by = null): MySQL_Handler
    {
        $sql = $this->build_sql_query($table, $column, $where, $join, $limit,
            $order_by, $group_by, self::SUM);
        $connect = $this->connect();
        $result = $connect->query($sql);
        $assoc = ['total' => 0];
        $check = $result && ($assoc = $result->fetch_assoc());
        return new MySQL_Handler(($check ? $assoc['total'] : 0), $check, $connect->error, $sql, $table);
    }

    /**
     * @param $table
     * @param $column
     * @param $where
     * @param $join
     * @param $limit
     * @param $order_by
     * @param $group_by
     * @param $query_type
     * @return bool|string
     */
    private function build_sql_query($table, $column, $where, $join, $limit, $order_by, $group_by, $query_type)
    {

        $query = ""; $where_query = ""; $join_query = "";

        $where_query_array = [];

        if (is_null($table) || empty($table)) return false;

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

                $columns = "";
                $values = "";

                if (is_array($column)) {
                    foreach ($column as $key => $value) {
                        if (empty($key)) continue;
                        $columns .= (empty($columns) ? '' : ', ') . $key;
                        $values .= (empty($values) ? '' : ", ") . ($value === null ? "null" : "'" . $value . "'");
                    }
                }

                if (!empty($columns) && !empty($values))
                    $query = "INSERT INTO {$table} ({$columns}) VALUES ({$values})";

                break;
            case self::DELETE:

                if (!empty($where_query)) $query = "DELETE FROM `{$table}` WHERE {$where_query}";
                else $query = "DELETE FROM `{$table}`";

                break;
            case self::SELECT:

                if (!empty($where_query) && !empty($join_query))
                    $query = "SELECT {$column} FROM `{$table}` {$join_query} WHERE {$where_query}";
                elseif (!empty($where_query) && empty($join_query))
                    $query = "SELECT {$column} FROM `{$table}` WHERE {$where_query}";
                elseif (empty($where_query) && !empty($join_query))
                    $query = "SELECT {$column} FROM `{$table}` {$join_query}";
                else $query = "SELECT {$column} FROM `{$table}`";

                break;
            case self::UPDATE:

                $updateColumn = "";

                if (is_array($column)) {

                    foreach ($column as $keys => $values) {
                        if (empty($keys)) continue;
                        $updateColumn .= (empty($updateColumn) ? '' : ', ') . $keys . " = " . ($values === null ? "null" : "'" . $values . "'");
                    }
                }

                if (!empty($where_query))
                    $query = "UPDATE `{$table}` SET {$updateColumn} WHERE {$where_query}";
                elseif (!empty($updateColumn)) $query = "UPDATE `{$table}` SET {$updateColumn}";

                break;
            case self::COUNT:

                if (!empty($where_query) && !empty($join_query))
                    $query = "SELECT COUNT({$column}) as total FROM `{$table}` {$join_query} WHERE {$where_query}";
                elseif (!empty($where_query) && empty($join_query))
                    $query = "SELECT COUNT({$column}) as total FROM `{$table}` WHERE {$where_query}";
                elseif (empty($where_query) && !empty($join_query))
                    $query = "SELECT COUNT({$column}) as total FROM `{$table}` {$join_query}";
                else $query = "SELECT COUNT({$column}) as total FROM `{$table}`";

                break;
            case self::AVG:

                if (!empty($where_query) && !empty($join_query))
                    $query = "SELECT AVG({$column}) as total FROM `{$table}` {$join_query} WHERE {$where_query}";
                elseif (!empty($where_query) && empty($join_query))
                    $query = "SELECT AVG({$column}) as total FROM `{$table}` WHERE {$where_query}";
                elseif (empty($where_query) && !empty($join_query))
                    $query = "SELECT AVG({$column}) as total FROM `{$table}` {$join_query}";
                else $query = "SELECT AVG({$column}) as total FROM `{$table}`";

                break;
            case self::SUM:

                if (!empty($where_query) && !empty($join_query))
                    $query = "SELECT SUM({$column}) as total FROM `{$table}` {$join_query} WHERE {$where_query}";
                elseif (!empty($where_query) && empty($join_query))
                    $query = "SELECT SUM({$column}) as total FROM `{$table}` WHERE {$where_query}";
                elseif (empty($where_query) && !empty($join_query))
                    $query = "SELECT SUM({$column}) as total FROM `{$table}` {$join_query}";
                else $query = "SELECT SUM({$column}) as total FROM `{$table}`";

                break;
            default:
                break;
        }

        if (is_string($group_by)) $query .= " GROUP BY {$group_by}";

        if (is_array($order_by))
            $query .= " ORDER BY " . key($order_by) . " " . current($order_by);

        if (is_numeric($limit)) $query .= " LIMIT " . $limit;
        elseif (is_array($limit))
            $query .= " LIMIT " . ($limit[0] <= 0 ? 0 : $limit[0]) . ', ' . ($limit[1] <= 0 ? 1 : $limit[1]);

        return trim($query);
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
                    foreach ($join_columns as $table_column => $external_table_column) {
                        $join_on .= (empty($join_on) ? '' : ' AND ') . "{$table_column} = {$external_table_column}";
                    }
                    if ($join_type == '~') $join_type = '';
                    $join_query .= (empty($join_query) ? '' : ' ') . "{$join_type} JOIN `{$join_table}` ON {$join_on}";
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

        if (is_array($column_value)) {

            $column_values = array_values($column_value);

            $expression = "";
            foreach ($column_values as $value)
                $expression .= $this->decode_column_expression($expression, 'OR', $table_column, $value);
            return (!empty($ref) ? " {$operator} " : '') .  "({$expression})";

        }

        $column_value = $column_value === null ? "null" : (str_contains($column_value, '(') ? $column_value : "'{$column_value}'");

        $col_operator = $this->getOperator($table_column) ?: '=';
        $table_column = $this->stripOperator($table_column) ?: $table_column;

        return (!empty($ref) ? " {$operator} " : '') . "{$table_column} {$col_operator} {$column_value}";
    }

    /**
     * @param $query
     * @return string
     */
    private function get_table_name(string $query)
    {

        $query = trim(str_replace(PHP_EOL, ' ', $query));

        $table = '';

        if (strtolower(substr($query, 0, 12)) == 'create table') {
            $start = stripos($query, 'CREATE TABLE') + 12;
            $end = strpos($query, '(');
            $length = $end - $start;
            $table = substr($query, $start, $length);
        } elseif (strtolower(substr($query, 0, 6)) == 'update') {
            $end = stripos($query, 'SET');
            $table = substr($query, 6, $end);
        } elseif (strtolower(substr($query, 0, 11)) == 'alter table') {
            $parts = explode(' ', $query);
            $table = $parts[2];
        } elseif (strtolower(substr($query, 0, 11)) == 'insert into') {
            $parts = explode(' ', $query);
            $table = $parts[2];
        } elseif (strtolower(substr($query, 0, 12)) == 'create index') {
            $parts = explode(' ', $query);
            $table = $parts[4];
        } elseif (strtolower(substr($query, 0, 6)) == 'select') {
            $parts = explode(' ', $query);
            foreach ($parts as $i => $part) {
                if (trim(strtolower($part)) == 'from') {
                    $table = $parts[$i + 1];
                    break;
                }
            }
        } elseif (strtolower(substr($query, 0, 29)) == 'create unique clustered index') {
            $parts = explode(' ', $query);
            $table = $parts[6];
        } elseif (strtolower(substr($query, 0, 22)) == 'create clustered index') {
            $parts = explode(' ', $query);
            $table = $parts[5];
        } elseif (strtolower(substr($query, 0, 15)) == 'exec sp_columns') {
            $parts = explode(' ', $query);
            $table = str_replace("'", '', $parts[2]);
        } elseif (strtolower(substr($query, 0, 11)) == 'delete from') {
            $parts = explode(' ', $query);
            $table = str_replace("'", '', $parts[2]);
        }

        return trim(str_replace(['`', '[', ']'], ['', '', ''], $table));

    }

    /**
     * @param $n
     * @param int $x
     * @return float|int
     */
    private function round_up_to_nearest($n, $x = 5)
    {
        return ($n % $x === 0 && !is_float(($n / $x))) ? round($n) : round((($n + $x / 2) / $x)) * $x;
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

    /**
     * @param array $data
     * @return array
     */
    private function filter_array(array $data): array
    {
        foreach ($data as $key => $value)
            $data[$key] = ($value === null ? null : addslashes($value));
        return $data;
    }

    /**
     * @param object $data
     * @return object
     */
    private function filter_object(object $data): object
    {
        foreach ($data as $key => $value)
            $data->{$key} = stripslashes($value);
        return $data;
    }
}