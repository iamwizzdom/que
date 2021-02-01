<?php


namespace que\database\observer;


use que\database\interfaces\model\Model;
use que\database\model\ModelCollection;

abstract class Observer implements \que\database\interfaces\observer\Observer
{
    /**
     * @var ObserverSignal
     */
    protected ObserverSignal $signal;

    protected ?string $modelKey = null;

    /**
     * Observer constructor.
     * @param ObserverSignal $signal
     */
    public function __construct(ObserverSignal $signal)
    {
        $this->signal = $signal;
    }

    public final function getSignal(): ObserverSignal
    {
        // TODO: Implement getSignal() method.
        return $this->signal;
    }

    /**
     * @return string|null
     */
    public final function getModelKey(): ?string
    {
        return $this->modelKey;
    }

    /**
     * @param Model $model
     * @return void
     */
    public abstract function onCreating(Model $model);

    /**
     * @param Model $model
     * @return void
     */
    public abstract function onCreated(Model $model);

    /**
     * @param Model $model
     * @param array $errors
     * @param $errorCode
     * @return void
     */
    public abstract function onCreateFailed(Model $model, array $errors, $errorCode);

    /**
     * This method is called when you signal retrial of the create operation and Que starts the retry operation
     * @param Model $model
     * @return void
     */
    public abstract function onCreateRetryStarted(Model $model);

    /**
     * This method is called when you signal retrial of the create operation and Que completes the retry operation
     * @param Model $model
     * @param bool $status
     * @param int $attempts
     * @return void
     */
    public abstract function onCreateRetryComplete(Model $model, bool $status, int $attempts);

    /**
     * @param ModelCollection $newModels
     * @param ModelCollection $oldModels
     * @return void
     */
    public abstract function onUpdating(ModelCollection $newModels, ModelCollection $oldModels);

    /**
     * @param ModelCollection $newModels
     * @param ModelCollection $oldModels
     * @return void
     */
    public abstract function onUpdated(ModelCollection $newModels, ModelCollection $oldModels);

    /**
     * @param ModelCollection $models
     * @param array $errors
     * @param $errorCode
     * @return void
     */
    public abstract function onUpdateFailed(ModelCollection $models, array $errors, $errorCode);

    /**
     * This method is called when you signal retrial of the update operation and Que starts the retry operation
     * @param ModelCollection $models
     * @return void
     */
    public abstract function onUpdateRetryStarted(ModelCollection $models);

    /**
     * This method is called when you signal retrial of the update operation and Que completes the retry operation
     * @param ModelCollection $models
     * @param bool $status
     * @param int $attempts
     * @return void
     */
    public abstract function onUpdateRetryComplete(ModelCollection $models, bool $status, int $attempts);

    /**
     * @param ModelCollection $models
     * @return void
     */
    public abstract function onDeleting(ModelCollection $models);

    /**
     * @param ModelCollection $models
     * @return void
     */
    public abstract function onDeleted(ModelCollection $models);

    /**
     * @param ModelCollection $models
     * @param array $errors
     * @param $errorCode
     * @return void
     */
    public abstract function onDeleteFailed(ModelCollection $models, array $errors, $errorCode);

    /**
     * This method is called when you signal retrial of the delete operation and Que starts the retry operation
     * @param ModelCollection $models
     * @return void
     */
    public abstract function onDeleteRetryStarted(ModelCollection $models);

    /**
     * This method is called when you signal retrial of the delete operation and Que completes the retry operation
     * @param ModelCollection $models
     * @param bool $status
     * @param int $attempts
     * @return void
     */
    public abstract function onDeleteRetryComplete(ModelCollection $models, bool $status, int $attempts);
}
