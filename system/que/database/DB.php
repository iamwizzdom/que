<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 10/21/2017
 * Time: 10:58 PM
 */

namespace que\database;

use Closure;
use que\database\connection\Connect;
use que\database\interfaces\Builder;
use que\database\interfaces\drivers\Driver;
use que\database\interfaces\drivers\DriverQueryBuilder;
use que\support\Config;

class DB extends Connect
{

    /**
     * @var DB
     */
    private static DB $instance;

    /**
     * DB constructor.
     */
    public function __construct()
    {
        $this->changeDriver(Config::get('database.default.driver', ''));
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        parent::__destruct();
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
     * @return DB
     */
    public static function getInstance(): DB
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    protected function getDriver(): Driver
    {
        return parent::getDriver(); // TODO: Change the autogenerated stub
    }

    /**
     * @param bool $mode
     * @return void
     */
    public function transStrict(bool $mode = true): void
    {
        $this->setTransStrict($mode);
    }

    /**
     * @param bool $testMode
     * @return bool
     */
    public function transStart(bool $testMode = false): bool
    {
        if (!$this->isTransEnabled()) return false;
        return $this->transBegin($testMode);
    }

    /**
     * @param bool $testMode
     * @return bool
     */
    public function transBegin(bool $testMode = false): bool
    {

        if (!$this->isTransEnabled()) return false;
        elseif ($this->getTransDepth() > 0) {
            $this->transDepth++;
            return true;
        }

        $this->setTransFailed(($testMode === true));

        if ($this->trans_begin()) {
            $this->setTransSuccessful(true);
            $this->transDepth++;
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function transComplete(): bool
    {
        if (!$this->isTransEnabled()) return false;

        if (!$this->isTransSuccessful() || $this->isTransFailed()) {
            $this->transRollBack();
            if (!$this->isTransStrict()) $this->setTransSuccessful(true);
            return false;
        }
        return $this->transCommit();
    }

    /**
     * @return bool
     */
    public function transCommit(): bool
    {
        if (!$this->isTransEnabled() || $this->getTransDepth() <= 0) return false;
        elseif ($this->getTransDepth() > 1 || $this->commit()) {
            $this->transDepth--;
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function transRollBack(): bool
    {
        if (!$this->isTransEnabled() || $this->getTransDepth() <= 0) return false;
        elseif ($this->getTransDepth() > 1 || $this->rollback()) {
            $this->transDepth--;
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function transRollBackAll()
    {
        if ($this->getTransDepth() > 0) $this->setTransSuccessful(false);
        $depth = null;
        while ($this->getTransDepth() !== 0) {
            $depth = $this->getTransDepth();
            $this->transComplete();
            if ($depth === $this->getTransDepth()) break;
        }
        return $depth !== null;
    }

    /**
     * @return bool
     */
    private function trans_begin(): bool
    {
        return $this->getDriver()->beginTransaction();
    }

    /**
     * @return bool
     */
    private function commit(): bool
    {
        return $this->getDriver()->commit();
    }

    /**
     * @return bool
     */
    private function rollback(): bool
    {
        return $this->getDriver()->rollback();
    }

    /**
     * @param string $table
     * @param array $columns
     * @return QueryResponse
     */
    public function insert(string $table, array $columns): QueryResponse
    {
        $driverBuilder = $this->getDriver()->getQueryBuilder();
        $driverBuilder->setQueryType(DriverQueryBuilder::INSERT);
        $builder = new QueryBuilder($this->getDriver(), $driverBuilder, $this);
        $builder->table($table);
        $builder->columns($columns);
        return $builder->exec();
    }

    /**
     * @return Builder
     */
    public function delete(): Builder
    {
        $driverBuilder = $this->getDriver()->getQueryBuilder();
        $driverBuilder->setQueryType(DriverQueryBuilder::DELETE);
        return new QueryBuilder($this->getDriver(), $driverBuilder, $this);
    }

    /**
     * @param string $table
     * @param Closure $callbackQuery
     * @return QueryResponse
     */
    public function check(string $table, Closure $callbackQuery): QueryResponse
    {
        $driverBuilder = $this->getDriver()->getQueryBuilder();
        $driverBuilder->setQueryType(DriverQueryBuilder::CHECK);
        $driverBuilder->setTable($table);
        $builder = new QueryBuilder($this->getDriver(), $driverBuilder, $this);
        $callbackQuery($builder);
        $builder->limit(1);
        return $builder->exec();
    }

    /**
     * @param string $table
     * @param $id
     * @param string $column
     * @param Closure|null $callbackQuery
     * @return QueryResponse
     */
    public function find(string $table, $id, string $column = 'id', Closure $callbackQuery = null): QueryResponse
    {
        $builder = $this->select();
        $builder->table($table);
        $builder->where($column, $id);
        $builder->limit(1);
        if ($callbackQuery !== null) $callbackQuery($builder);
        return $builder->exec();
    }

    /**
     * @param string $table
     * @param null $id
     * @param string $column
     * @param Closure|null $callbackQuery
     * @return QueryResponse
     */
    public function findAll(string $table, $id = null, string $column = 'id', Closure $callbackQuery = null): QueryResponse
    {
        $builder = $this->select();
        $builder->table($table);
        if ($id !== null) $builder->where($column, $id);
        if ($callbackQuery !== null) $callbackQuery($builder);
        return $builder->exec();
    }

    /**
     * @param array $columns
     * @return Builder
     */
    public function select(...$columns): Builder
    {
        $driverBuilder = $this->getDriver()->getQueryBuilder();
        $driverBuilder->setQueryType(DriverQueryBuilder::SELECT);
        $builder = new QueryBuilder($this->getDriver(), $driverBuilder, $this);
        if (!empty($columns)) $builder->select(...$columns);
        return $builder;
    }

    /**
     * @param string $table
     * @param string $query
     * @param array $queryBindings
     * @return QueryResponse
     */
    public function raw_select(string $table, string $query, array $queryBindings): QueryResponse
    {
        $driverBuilder = $this->getDriver()->getQueryBuilder();
        $driverBuilder->setQueryType(DriverQueryBuilder::RAW_SELECT);
        $driverBuilder->setTable($table);
        $driverBuilder->setQuery($query);
        $driverBuilder->setQueryBindings($queryBindings);
        return (new QueryBuilder($this->getDriver(), $driverBuilder, $this))->exec();
    }

    /**
     * @param string $table
     * @param string $query
     * @param array $queryBindings
     * @return QueryResponse
     */
    public function raw_query(string $table, string $query, array $queryBindings): QueryResponse
    {
        $driverBuilder = $this->getDriver()->getQueryBuilder();
        $driverBuilder->setQueryType(DriverQueryBuilder::RAW_QUERY);
        $driverBuilder->setTable($table);
        $driverBuilder->setQuery($query);
        $driverBuilder->setQueryBindings($queryBindings);
        return (new QueryBuilder($this->getDriver(), $driverBuilder, $this))->exec();
    }

    /**
     * @param string $table
     * @param string $query
     * @param array $queryBindings
     * @return QueryResponse
     */
    public function raw_object(string $table, string $query, array $queryBindings): QueryResponse
    {
        $driverBuilder = $this->getDriver()->getQueryBuilder();
        $driverBuilder->setQueryType(DriverQueryBuilder::RAW_OBJECT);
        $driverBuilder->setTable($table);
        $driverBuilder->setQuery($query);
        $driverBuilder->setQueryBindings($queryBindings);
        return (new QueryBuilder($this->getDriver(), $driverBuilder, $this))->exec();
    }

    /**
     * @return Builder
     */
    public function update(): Builder
    {
        $driverBuilder = $this->getDriver()->getQueryBuilder();
        $driverBuilder->setQueryType(DriverQueryBuilder::UPDATE);
        return new QueryBuilder($this->getDriver(), $driverBuilder, $this);
    }

    /**
     * @param string|null $table
     * @param string $column
     * @return Builder
     */
    public function count(string $table = null, $column = null): Builder
    {
        $driverBuilder = $this->getDriver()->getQueryBuilder();
        $driverBuilder->setQueryType(DriverQueryBuilder::COUNT);
        if ($table !== null) $driverBuilder->setTable($table);
        if ($column !== null) $driverBuilder->setSelect($column);
        return new QueryBuilder($this->getDriver(), $driverBuilder, $this);
    }

    /**
     * @param string|null $table
     * @param string $column
     * @return Builder
     */
    public function avg(string $table = null, $column = null): Builder
    {
        $driverBuilder = $this->getDriver()->getQueryBuilder();
        $driverBuilder->setQueryType(DriverQueryBuilder::AVG);
        if ($table !== null) $driverBuilder->setTable($table);
        if ($column !== null) $driverBuilder->setSelect($column);
        return new QueryBuilder($this->getDriver(), $driverBuilder, $this);
    }

    /**
     * @param string|null $table
     * @param string $column
     * @return Builder
     */
    public function sum(string $table = null, $column = null): Builder
    {
        $driverBuilder = $this->getDriver()->getQueryBuilder();
        $driverBuilder->setQueryType(DriverQueryBuilder::SUM);
        if ($table !== null) $driverBuilder->setTable($table);
        if ($column !== null) $driverBuilder->setSelect($column);
        return new QueryBuilder($this->getDriver(), $driverBuilder, $this);
    }

    /**
     * @param string $table
     * @return QueryResponse
     */
    private function show_table(string $table): QueryResponse
    {
        $driverBuilder = $this->getDriver()->getQueryBuilder();
        $driverBuilder->setQueryType(DriverQueryBuilder::SHOW);
        $builder = new QueryBuilder($this->getDriver(), $driverBuilder, $this);
        $builder->table($table);
        return $builder->exec();
    }
}
