<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 5/9/2020
 * Time: 10:39 PM
 */

namespace que\database\interfaces\drivers;


class ObserverSignal
{
    /**
     * @var bool
     */
    private bool $continue = true;

    /**
     * @var bool
     */
    private bool $undo = false;

    /**
     * @var bool
     */
    private bool $retry = false;

    /**
     * @var int
     */
    private int $trials = 0;

    /**
     * @var float
     */
    private float $interval = 0.1;

    /**
     * @return bool
     */
    public function continueOperation(): bool {
        return $this->continue;
    }

    /**
     * @param bool $continue
     */
    public function setContinueOperation(bool $continue): void {
        $this->continue = $continue;
    }

    /**
     * @return bool
     */
    public function undoOperation(): bool {
        return $this->undo;
    }

    /**
     * @param bool $undo
     */
    public function setUndoOperation(bool $undo): void {
        $this->undo = $undo;
    }

    /**
     * @return bool
     */
    public function retryOperation(): bool {
        return $this->retry;
    }

    /**
     * @return int
     */
    public function getTrials(): int
    {
        return $this->trials;
    }

    /**
     * @return float
     */
    public function getInterval(): float
    {
        return $this->interval;
    }

    /**
     * @param bool $retry
     * @param int $trials | number of times you want to retry the operation
     * @param float $interval | retrial interval in milliseconds
     * @return mixed
     */
    public function setRetryOperation(bool $retry, int $trials = 1, float $interval = 0.1) {
        $this->retry = $retry;
        $this->trials = $trials;
        $this->interval = $interval;
    }
}