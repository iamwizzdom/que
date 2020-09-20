<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 5/9/2020
 * Time: 12:53 AM
 */

namespace que\database\interfaces\drivers;


use que\database\interfaces\model\Model;
use que\database\model\ModelCollection;

interface Observer
{
    /**
     * Observer constructor.
     * @param ObserverSignal $signal
     */
    public function __construct(ObserverSignal $signal);

    /**
     * @param Model $model
     * @return mixed
     */
    public function onCreating(Model $model);

    /**
     * @param Model $model
     * @return mixed
     */
    public function onCreated(Model $model);

    /**
     * @param Model $model
     * @param array $errors
     * @param $errorCode
     * @return mixed
     */
    public function onCreateFailed(Model $model, array $errors, $errorCode);

    /**
     * This method is called when you signal retrial of the create operation and Que starts the retry operation
     * @param Model $model
     * @return mixed
     */
    public function onCreateRetryStarted(Model $model);

    /**
     * This method is called when you signal retrial of the create operation and Que completes the retry operation
     * @param Model $model
     * @param bool $status
     * @param int $attempts
     * @return mixed
     */
    public function onCreateRetryComplete(Model $model, bool $status, int $attempts);

    /**
     * @param ModelCollection $model
     * @return mixed
     */
    public function onUpdating(ModelCollection $model);

    /**
     * @param ModelCollection $newModels
     * @param ModelCollection $previousModels
     * @return mixed
     */
    public function onUpdated(ModelCollection $newModels, ModelCollection $previousModels);

    /**
     * @param ModelCollection $models
     * @param array $errors
     * @param $errorCode
     * @return mixed
     */
    public function onUpdateFailed(ModelCollection $models, array $errors, $errorCode);

    /**
     * This method is called when you signal retrial of the update operation and Que starts the retry operation
     * @param ModelCollection $models
     * @return mixed
     */
    public function onUpdateRetryStarted(ModelCollection $models);

    /**
     * This method is called when you signal retrial of the update operation and Que completes the retry operation
     * @param ModelCollection $models
     * @param bool $status
     * @param int $attempts
     * @return mixed
     */
    public function onUpdateRetryComplete(ModelCollection $models, bool $status, int $attempts);

    /**
     * @param ModelCollection $models
     * @return mixed
     */
    public function onDeleting(ModelCollection $models);

    /**
     * @param ModelCollection $models
     * @return mixed
     */
    public function onDeleted(ModelCollection $models);

    /**
     * @param ModelCollection $models
     * @param array $errors
     * @param $errorCode
     * @return mixed
     */
    public function onDeleteFailed(ModelCollection $models, array $errors, $errorCode);

    /**
     * This method is called when you signal retrial of the delete operation and Que starts the retry operation
     * @param ModelCollection $models
     * @return mixed
     */
    public function onDeleteRetryStarted(ModelCollection $models);

    /**
     * This method is called when you signal retrial of the delete operation and Que completes the retry operation
     * @param ModelCollection $models
     * @param bool $status
     * @param int $attempts
     * @return mixed
     */
    public function onDeleteRetryComplete(ModelCollection $models, bool $status, int $attempts);

    /**
     * @return ObserverSignal
     */
    public function getSignal(): ObserverSignal;
}