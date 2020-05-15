<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 5/3/2020
 * Time: 10:07 PM
 */

namespace que\database\interfaces;


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
     * @param $table
     * @param $columns
     * @param null $where
     * @param null $join
     * @param null $limit
     * @param null $order_by
     * @param null $group_by
     */
    public function buildQuery($table, $columns, $where = null, $join = null, $limit = null, $order_by = null, $group_by = null): void;

    /**
     * @param bool $paginated
     * @return mixed
     */
    public function setPaginated(bool $paginated);

    /**
     * @return bool
     */
    public function isPaginated(): bool;

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
     * @param array $values
     */
    public function setQueryBindValues(array $values): void;

    /**
     * @return array
     */
    public function getQueryBindValues(): array;

}