<?php

use que\common\exception\PreviousException;
use que\common\exception\QueException;
use que\http\HTTP;
use que\support\Arr;

/**
 * Created by PhpStorm.
 * User: wisdom Emenike
 * Date: 18/12/2021
 * Time: 8:26 PM
 */

class Events
{
    /**
     * @var array
     */
    private array $events = [];

    /**
     * @var Events
     */
    private static Events $instance;

    protected function __construct()
    {
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    public function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

    /**
     * @return Events
     */
    private static function getInstance(): Events
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param string $event
     * @return bool
     */
    public function has(string $event): bool
    {
        return isset($this->events[$event]);
    }

    /**
     * @param string $event
     * @param callable $listener
     * @param bool $runOnce
     */
    public function push(string $event, callable $listener, bool $runOnce = false)
    {
        $this->events[$event][] = [
            'runOnce' => $runOnce,
            'listener' => $listener
        ];
    }

    /**
     * @param string $event
     * @param ...$params
     * @throws QueException
     */
    public function exec(string $event, ...$params)
    {
        if (!$this->has($event)) {
            throw new QueException("Event with key '$event' does not exist.", "Event error",
                HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(2));
        }

        $events = $this->events[$event];

        foreach ($events as $key => $eventItem) {
            $runOnce = $eventItem['runOnce'];
            $listener = $eventItem['listener'];
            $listener(...$params);
            if ($runOnce) unset($events[$key]);
        }

        if (empty($events)) {
            unset($this->events[$event]);
            return;
        }

        $this->events[$event] = $events;
    }

    /**
     * @param string $event
     * @param callable $listener
     */
    public static function on(string $event, callable $listener)
    {
        $events = self::getInstance();
        $events->push($event, $listener);
    }

    /**
     * @param string $event
     * @param callable $listener
     */
    public static function once(string $event, callable $listener)
    {
        $events = self::getInstance();
        $events->push($event, $listener, true);
    }

    /**
     * @param string $event
     * @param ...$params
     * @throws QueException
     */
    public static function emit(string $event, ...$params)
    {
        $events = self::getInstance();
        $events->exec($event, ...$params);
    }
}