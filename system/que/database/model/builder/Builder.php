<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 6/1/2020
 * Time: 8:39 AM
 */

namespace que\database\model\builder;


use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\database\interfaces\model\Model;
use que\database\model\ModelStack;

class Builder
{
    const NEXT = 1101;
    const PREVIOUS = 1102;

    /**
     * Builder constructor.
     * @param Model $model
     * @param int $type
     */
    public function __construct(Model $model, int $type)
    {
        $this->setModel($model);
        $this->setType($type);
    }

    /**
     * @var Model
     */
    private Model $model;

    /**
     * @var int
     */
    private int $type;

    /**
     * @var string
     */
    private string $primaryKey = '';

    /**
     * @var mixed
     */
    private $columns = null;

    /**
     * @var string
     */
    private string $dataType = 'model';

    /**
     * @var array
     */
    private array $join = [];

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @param Model $model
     */
    private function setModel(Model $model): void
    {
        $this->model = $model;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $direction
     */
    private function setType(int $direction): void
    {
        $this->type = $direction;
    }

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * @param string $primaryKey
     * @return Builder
     */
    public function setPrimaryKey(string $primaryKey): Builder
    {
        $this->primaryKey = $primaryKey;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param mixed $columns
     * @return Builder
     */
    public function setColumns($columns): Builder
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * @return string
     */
    public function getDataType(): string
    {
        return $this->dataType;
    }

    /**
     * @param string $dataType
     * @return Builder
     */
    public function setDataType(string $dataType): Builder
    {
        $this->dataType = $dataType;
        return $this;
    }

    /**
     * @return array
     */
    public function getJoin(): array
    {
        return $this->join;
    }

    /**
     * @param array $join
     * @return Builder
     */
    public function setJoin(array $join): Builder
    {
        $this->join = $join;
        return $this;
    }

    /**
     * @return array|object|Model|ModelStack|null
     */
    public function get() {

        $primaryKey = !empty($this->getPrimaryKey()) ? $this->getPrimaryKey() : $this->model->getPrimaryKey();

        if (!$this->getModel()->offsetExists($primaryKey)) {
            throw new QueRuntimeException("Invalid primary key: No value was found for key '{$primaryKey}'",
                "Model error", 0, HTTP_INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));
        }

        if (!$this->getModel()->get($primaryKey)->isNumeric()) {
            throw new QueRuntimeException("Invalid primary key: Value for key '{$primaryKey}' is expected to be numeric, got {$this->get($primaryKey)->getType()}",
                "Model error", 0, HTTP_INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));
        }

        $record = db()->select($this->model->getTable(), $this->getColumns() ?? '*', [
            'AND' => [
                "{$primaryKey}[" . ($this->getType() === self::PREVIOUS ? '<' : '>') . "]" =>
                    $this->getModel()->getValue($primaryKey)
            ]
        ], !empty($this->getJoin()) ? $this->getJoin() : null, 1,
            $this->getType() === self::PREVIOUS ? [$primaryKey => 'DESC'] : null);

        if (!$record->isSuccessful()) return null;

        switch (strtolower($this->getDataType())) {
            case 'model':
                return $record->getQueryResponseWithModel(config('database.default.model'), 0);
            case 'array':
                return $record->getQueryResponseArray(0);
            default:
                return $record->getQueryResponse(0);
        }
    }
}