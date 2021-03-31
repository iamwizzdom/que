<?php


namespace relation;


use que\database\interfaces\model\Model;
use que\database\model\ModelCollection;

trait DbMapper
{
    /**
     * @param string $table
     * @param string $foreignValue
     * @param string $primaryKey
     * @param string $modelKey
     * @return Model|null
     */
    public function oneToOne(string $table, string $foreignValue, string $primaryKey, string $modelKey): ?Model
    {
        $response = db()->find($table, $foreignValue, $primaryKey);
        $response->setModelKey($modelKey);
        return $response->isSuccessful() ? $response->getFirstWithModel() : null;
    }

    /**
     * @param string $table
     * @param string $foreignKey
     * @param string $primaryValue
     * @param string $modelKey
     * @return Model|null
     */
    public function oneToOneRevered(string $table, string $primaryValue, string $foreignKey, string $modelKey): ?Model
    {
        $response = db()->find($table, $primaryValue, $foreignKey);
        $response->setModelKey($modelKey);
        return $response->isSuccessful() ? $response->getFirstWithModel() : null;
    }

    /**
     * @param string $table
     * @param string $primaryValue
     * @param string $foreignKey
     * @param string $modelKey
     * @return ModelCollection|null
     */
    public function oneToMany(string $table, string $primaryValue, string $foreignKey, string $modelKey): ?ModelCollection
    {
        $response = db()->findAll($table, $primaryValue, $foreignKey);
        $response->setModelKey($modelKey);
        return $response->isSuccessful() ? $response->getAllWithModel() : null;
    }
}
