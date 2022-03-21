<?php
/**
 * Created by PhpStorm.
 * User: wisdom Emenike
 * Date: 27/01/2022
 * Time: 11:00 AM
 */

namespace que\queue;

trait Queueable
{
    private int $delay = 0;
    private bool $afterDbCommit = false;

    /**
     * @return int
     */
    public function getDelay(): int
    {
        return $this->delay;
    }

    /**
     * Number of seconds to wait before the job is made available
     * @param int $delay
     */
    public function setDelay(int $delay): void
    {
        $this->delay = $delay;
    }

    public function afterDbCommit(): void
    {
        $this->afterDbCommit = true;
    }

    /**
     * @return bool
     */
    public function isAfterDbCommit(): bool
    {
        return $this->afterDbCommit;
    }
}