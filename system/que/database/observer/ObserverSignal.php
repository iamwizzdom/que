<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 5/9/2020
 * Time: 10:39 PM
 */

namespace que\database\observer;


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
     * @var string|null
     */
    private ?string $reason = null;

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

    /**
     * @param string|null $reason
     */
    public function discontinueOperation(string $reason = null): void {
        $this->continue = false;
        $this->reason = $reason;
    }

    /**
     * @return bool
     */
    public function isUndoOperation(): bool {
        return $this->undo;
    }

    /**
     * @param string|null $reason
     */
    public function undoOperation(string $reason = null): void {
        $this->undo = true;
        $this->reason = $reason;
    }

    /**
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
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
     * @return void
     */
    public function retryOperation(int $trials = 1, float $interval = 0.1) {
        $this->retry = true;
        $this->trials = $trials;
        $this->interval = $interval;
    }
}
