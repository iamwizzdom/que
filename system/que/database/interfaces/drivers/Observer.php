<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 5/9/2020
 * Time: 12:53 AM
 */

namespace que\database\interfaces\drivers;


use que\database\interfaces\model\Model;
use que\database\model\ModelStack;

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
     * @param ModelStack $model
     * @return mixed
     */
    public function onUpdating(ModelStack $model);

    /**
     * @param ModelStack $newModels
     * @param ModelStack $previousModels
     * @return mixed
     */
    public function onUpdated(ModelStack $newModels, ModelStack $previousModels);

    /**
     * @param ModelStack $models
     * @param array $errors
     * @param $errorCode
     * @return mixed
     */
    public function onUpdateFailed(ModelStack $models, array $errors, $errorCode);

    /**
     * This method is called when you signal retrial of the update operation and Que starts the retry operation
     * @param ModelStack $models
     * @return mixed
     */
    public function onUpdateRetryStarted(ModelStack $models);

    /**
     * This method is called when you signal retrial of the update operation and Que completes the retry operation
     * @param ModelStack $models
     * @param bool $status
     * @param int $attempts
     * @return mixed
     */
    public function onUpdateRetryComplete(ModelStack $models, bool $status, int $attempts);

    /**
     * @param ModelStack $models
     * @return mixed
     */
    public function onDeleting(ModelStack $models);

    /**
     * @param ModelStack $models
     * @return mixed
     */
    public function onDeleted(ModelStack $models);

    /**
     * @param ModelStack $models
     * @param array $errors
     * @param $errorCode
     * @return mixed
     */
    public function onDeleteFailed(ModelStack $models, array $errors, $errorCode);

    /**
     * This method is called when you signal retrial of the delete operation and Que starts the retry operation
     * @param ModelStack $models
     * @return mixed
     */
    public function onDeleteRetryStarted(ModelStack $models);

    /**
     * This method is called when you signal retrial of the delete operation and Que completes the retry operation
     * @param ModelStack $models
     * @param bool $status
     * @param int $attempts
     * @return mixed
     */
    public function onDeleteRetryComplete(ModelStack $models, bool $status, int $attempts);

    /**
     * @return ObserverSignal
     */
    public function getSignal(): ObserverSignal;
}