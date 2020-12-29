<?php


namespace relation;


use que\database\interfaces\model\Model;
use que\database\model\ModelCollection;

trait DbMapper
{
    /**
     * @param string $table
     * @param string $foreignKey
     * @param string $primaryKey
     * @param string $modelKey
     * @return Model|null
     */
    public function oneToOne(string $table, string $foreignKey, string $primaryKey, string $modelKey): ?Model
    {
        $response = db()->find($table, $foreignKey, $primaryKey);
        $response->setModelKey($modelKey);
        return $response->isSuccessful() ? $response->getFirstWithModel() : null;
    }

    /**
     * @param string $table
     * @param string $foreignKey
     * @param string $primaryKey
     * @param string $modelKey
     * @return ModelCollection|null
     */
    public function oneToMany(string $table, string $foreignKey, string $primaryKey, string $modelKey): ?ModelCollection
    {
        $response = db()->findAll($table, $foreignKey, $primaryKey);
        $response->setModelKey($modelKey);
        return $response->isSuccessful() ? $response->getAllWithModel() : null;
    }
}
