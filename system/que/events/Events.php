<?php

use que\cache\Cache;
use que\common\exception\PreviousException;
use que\common\exception\QueException;
use que\http\HTTP;

/**
 * Created by PhpStorm.
 * User: wisdom Emenike
 * Date: 18/12/2021
 * Time: 8:26 PM
 */

class Events
{
    /**
     * @var Cache
     */
    private Cache $cache;

    /**
     * @var Events
     */
    private static Events $instance;

    protected function __construct()
    {
        $this->cache = Cache::getInstance();
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
        return $this->cache->isset($event);
    }

    /**
     * @param string $event
     * @param callable $listener
     * @param bool $runOnce
     */
    public function push(string $event, callable $listener, bool $runOnce = false)
    {
        $this->cache->rPush($event, ['runOnce' => $runOnce, 'listener' => $listener]);
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

        while ($eventItem = $this->cache->lPop($event)) {
            $runOnce = $eventItem['runOnce'];
            $listener = $eventItem['listener'];
            $listener(...$params);
            if (!$runOnce) $this->push($event, $listener);
        }
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