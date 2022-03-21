<?php
/**
 * Created by PhpStorm.
 * User: wisdom Emenike
 * Date: 27/01/2022
 * Time: 8:28 PM
 */

namespace que\queue;

use que\http\HTTP;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;

class PendingDispatcher
{
    private Job $job;
    private bool $handleNow = false;
    private static Dispatcher $dispatcher;

    /**
     * @param $job
     */
    public function __construct($job)
    {
        if (!$job instanceof Job) {
            throw new QueRuntimeException(
                "Trying to dispatch an invalid job object",
                "Job Queue Error", E_USER_ERROR,
                HTTP::INTERNAL_SERVER_ERROR,
                PreviousException::getInstance(3)
            );
        }
        if (!isset(self::$dispatcher)) {
            self::$dispatcher = new Dispatcher();
        }
        $this->job = $job;
    }

    public function setDelay(int $delay): static
    {
        $this->job->setDelay($delay);
        return $this;
    }

    public function afterDbCommit(): static
    {
        $this->job->afterDbCommit();
        return $this;
    }

    public function handleNow(): static
    {
        $this->handleNow = true;
        return $this;
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        if ($this->handleNow) {
            self::$dispatcher->dispatchNow($this->job);
        } else {
            self::$dispatcher->dispatch($this->job);
        }
    }
}