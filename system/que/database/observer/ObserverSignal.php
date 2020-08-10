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
    public function isContinueOperation(): bool {
        return $this->continue;
    }

    public function discontinueOperation(): void {
        $this->continue = false;
    }

    /**
     * @return bool
     */
    public function isUndoOperation(): bool {
        return $this->undo;
    }

    public function undoOperation(): void {
        $this->undo = true;
    }

    /**
     * @return bool
     */
    public function isRetryOperation(): bool {
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
     * @param int $trials | number of times you want to retry the operation
     * @param float $interval | retrial interval in milliseconds
     * @return mixed
     */
    public function retryOperation(int $trials = 1, float $interval = 0.1) {
        $this->retry = true;
        $this->trials = $trials;
        $this->interval = $interval;
    }
}
