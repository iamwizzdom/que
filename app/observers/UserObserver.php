<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 5/10/2020
 * Time: 11:09 PM
 */

namespace observers;


use que\database\interfaces\Observer;
use que\database\interfaces\ObserverSignal;
use que\database\model\interfaces\Model;
use que\database\model\ModelStack;

class UserObserver implements Observer
{

    private ObserverSignal $signal;

    /**
     * @inheritDoc
     */
    public function __construct(ObserverSignal $signal)
    {
        $this->signal = $signal;
    }

    /**
     * @inheritDoc
     */
    public function onCreating(Model $model)
    {
        // TODO: Implement onCreating() method.
        debug_print([$model,'onCreating']);
//        $this->getSignal()->setContinueOperation(false);
    }

    /**
     * @inheritDoc
     */
    public function onCreated(Model $model)
    {
        // TODO: Implement onCreated() method.
        debug_print([$model,'onCreated']);
//        $this->getSignal()->setUndoOperation(true);
    }

    /**
     * @inheritDoc
     */
    public function onCreateFailed(Model $model, array $errors, $errorCode)
    {
        // TODO: Implement onCreateFailed() method.
        $this->getSignal()->setRetryOperation(true, 2);
        debug_print(['onCreateFailed' => $model, $errors, $errorCode]);
    }

    /**
     * @inheritDoc
     */
    public function onCreateRetryStarted(Model $model)
    {
        // TODO: Implement onCreateRetryStarted() method.
        debug_print(['onCreateRetryStarted' => $model]);
        $model->offsetRename('names', 'name');
    }

    /**
     * @inheritDoc
     */
    public function onCreateRetryComplete(Model $model, bool $status, int $attempts)
    {
        // TODO: Implement onCreateRetryComplete() method.
        debug_print(['onCreateRetryComplete' => $model, $status ? 'true' : 'false', $attempts]);
    }

    /**
     * @inheritDoc
     */
    public function onUpdating(ModelStack $models)
    {
        // TODO: Implement onUpdating() method.
        debug_print([$models,'onUpdating']);
    }

    /**
     * @inheritDoc
     */
    public function onUpdated(ModelStack $newModels, ModelStack $previousModels)
    {
        // TODO: Implement onUpdated() method.
        debug_print([['$newModels' => $newModels], ['$previousModels' => $previousModels], 'onUpdated']);
        $this->getSignal()->setUndoOperation(true);
    }

    /**
     * @inheritDoc
     */
    public function onUpdateFailed(ModelStack $models, array $errors, $errorCode)
    {
        // TODO: Implement onUpdateFailed() method.
        $this->getSignal()->setRetryOperation(true, 1);
        debug_print(['onUpdateFailed' => $models, $errors, $errorCode]);
    }

    /**
     * @inheritDoc
     */
    public function onUpdateRetryStarted(ModelStack $models)
    {
        // TODO: Implement onUpdateRetryStarted() method.
        debug_print(['onUpdateRetryStarted' => $models]);
    }

    /**
     * @inheritDoc
     */
    public function onUpdateRetryComplete(ModelStack $models, bool $status, int $attempts)
    {
        // TODO: Implement onUpdateRetryComplete() method.
        debug_print(['onUpdateRetryComplete' => $models, $status ? 'true' : 'false', $attempts]);
    }

    /**
     * @inheritDoc
     */
    public function onDeleting(ModelStack $models)
    {
        // TODO: Implement onDeleting() method.
//        $models->unsetWhen(function (Model $model) {
//            return $model->get('id')->isEqualToAny([75,76]);
//        });
        debug_print([$models,'onDeleting']);
    }

    /**
     * @inheritDoc
     */
    public function onDeleted(ModelStack $models)
    {
        // TODO: Implement onDeleted() method.
        debug_print([$models,'onDeleted']);
//        $this->getSignal()->setUndoOperation(true);
    }

    /**
     * @inheritDoc
     */
    public function onDeleteFailed(ModelStack $models, array $errors, $errorCode)
    {
        // TODO: Implement onDeleteFailed() method.
        $this->getSignal()->setRetryOperation(true, 3);
        debug_print(['onDeleteFailed' => $models, $errors, $errorCode]);
    }

    /**
     * @inheritDoc
     */
    public function onDeleteRetryStarted(ModelStack $models)
    {
        // TODO: Implement onDeleteRetryStarted() method.
        debug_print(['changed db obs', db()->changeDb('que') ? 'true' : 'false']);
        debug_print(['onDeleteRetryStarted' => $models, config('database.connections.mysql.dbname')]);
    }

    /**
     * @inheritDoc
     */
    public function onDeleteRetryComplete(ModelStack $models, bool $status, int $attempts)
    {
        // TODO: Implement onDeleteRetryComplete() method.
        debug_print(['onDeleteRetryComplete' => $models, $status ? 'true' : 'false', $attempts]);
    }

    /**
     * @inheritDoc
     */
    public function getSignal(): ObserverSignal
    {
        // TODO: Implement getSignal() method.
        return $this->signal;
    }
}