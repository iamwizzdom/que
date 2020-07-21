<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 6/4/2020
 * Time: 7:14 AM
 */

namespace que\database\mysql;


class QueryBuilder
{
    /**
     * @var bool
     */
    private bool $paginate = false;

    /**
     * @var string
     */
    private string $tag = "";

    /**
     * @var int
     */
    private int $page = 0;

    /**
     * @var int
     */
    private int $recordPerPage = 0;

    /**
     * @var string
     */
    private string $table;

    /**
     * @var array
     */
    private array $columns = [];

    /**
     * @var array
     */
    private array $columnBindValues = [];

    /**
     * @var array
     */
    private array $where = [];

    /**
     * @var array
     */
    private array $join = [];

    /**
     * @var array|null
     */
    private ?array $order_by = null;

    /**
     * @var string|null
     */
    private ?string $group_by = null;

    /**
     * @var mixed|null
     */
    private $limit = null;

    /**
     * QueryBuilder constructor.
     * @param string $table
     */
    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     * @param array $columns
     */
    public function columns(array $columns)
    {
        $this->columns = array_merge($this->columns, $columns);
    }

    /**
     * @param array $values
     */
    public function columnBindValues(array $values)
    {
        $this->columnBindValues = array_merge($this->columnBindValues, $values);
    }

    /**
     * @param array $where
     */
    public function where(array $where)
    {
        $this->where = array_merge_recursive($this->where, $where);
    }

    /**
     * @param array $join
     */
    public function join(array $join)
    {
        $this->join = array_merge_recursive($this->join, $join);
    }

    /**
     * @param string $column
     * @param string $direction
     */
    public function orderBy(string $column, string $direction = 'asc') {
        $this->order_by = [$column => $direction];
    }

    /**
     * @param string $column
     */
    public function groupBy(string $column)
    {
        $this->group_by = $column;
    }

    /**
     * @param int $limit
     * @param int|null $offset
     */
    public function limit(int $limit, int $offset = null)
    {
        if ($offset !== null) $this->limit = [$offset, $limit];
        else $this->limit = $limit;
    }
}