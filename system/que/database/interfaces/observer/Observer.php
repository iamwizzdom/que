<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 5/9/2020
 * Time: 12:53 AM
 */

namespace que\database\interfaces\observer;


use que\database\interfaces\model\Model;
use que\database\model\ModelCollection;
use que\database\observer\ObserverSignal;

interface Observer
{
    /**
     * Observer constructor.
     * @param ObserverSignal $signal
     */
    public function __construct(ObserverSignal $signal);

    /**
     * @param Model $model
     * @return void
     */
    public function onCreating(Model $model);

    /**
     * @param Model $model
     * @return void
     */
    public function onCreated(Model $model);

    /**
     * @param Model $model
     * @param array $errors
     * @param $errorCode
     * @return void
     */
    public function onCreateFailed(Model $model, array $errors, $errorCode);

    /**
     * This method is called when you signal retrial of the create operation and Que starts the retry operation
     * @param Model $model
     * @return void
     */
    public function onCreateRetryStarted(Model $model);

    /**
     * This method is called when you signal retrial of the create operation and Que completes the retry operation
     * @param Model $model
     * @param bool $status
     * @param int $attempts
     * @return void
     */
    public function onCreateRetryComplete(Model $model, bool $status, int $attempts);

    /**
     * @param ModelCollection $newModels
     * @param ModelCollection $oldModels
     * @return void
     */
    public function onUpdating(ModelCollection $newModels, ModelCollection $oldModels);

    /**
     * @param ModelCollection $newModels
     * @param ModelCollection $oldModels
     * @return void
     */
    public function onUpdated(ModelCollection $newModels, ModelCollection $oldModels);

    /**
     * @param ModelCollection $models
     * @param array $errors
     * @param $errorCode
     * @return void
     */
    public function onUpdateFailed(ModelCollection $models, array $errors, $errorCode);

    /**
     * This method is called when you signal retrial of the update operation and Que starts the retry operation
     * @param ModelCollection $models
     * @return void
     */
    public function onUpdateRetryStarted(ModelCollection $models);

    /**
     * This method is called when you signal retrial of the update operation and Que completes the retry operation
     * @param ModelCollection $models
     * @param bool $status
     * @param int $attempts
     * @return void
     */
    public function onUpdateRetryComplete(ModelCollection $models, bool $status, int $attempts);

    /**
     * @param ModelCollection $models
     * @return void
     */
    public function onDeleting(ModelCollection $models);

    /**
     * @param ModelCollection $models
     * @return void
     */
    public function onDeleted(ModelCollection $models);

    /**
     * @param ModelCollection $models
     * @param array $errors
     * @param $errorCode
     * @return void
     */
    public function onDeleteFailed(ModelCollection $models, array $errors, $errorCode);

    /**
     * This method is called when you signal retrial of the delete operation and Que starts the retry operation
     * @param ModelCollection $models
     * @return void
     */
    public function onDeleteRetryStarted(ModelCollection $models);

    /**
     * This method is called when you signal retrial of the delete operation and Que completes the retry operation
     * @param ModelCollection $models
     * @param bool $status
     * @param int $attempts
     * @return void
     */
    public function onDeleteRetryComplete(ModelCollection $models, bool $status, int $attempts);

    /**
     * @return ObserverSignal
     */
    public function getSignal(): ObserverSignal;
}