<?php

use Opis\Closure\SerializableClosure;
use que\cache\Cache;
use que\common\exception\PreviousException;
use que\common\exception\QueException;
use que\http\HTTP;
use que\support\Arr;
use que\utility\hash\Hash;

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
     * @param callable|string $listener
     * @param string|int|null $flag
     * @param bool $runOnce
     */
    public function push(string $event, callable|string $listener, string|int|null $flag, bool $runOnce = false)
    {
        $listener = is_string($listener) ? $listener : serialize(new SerializableClosure($listener));
        $this->cache->rPush($event, ['runOnce' => $runOnce, 'listener' => $listener, 'flag' => $flag]);
    }

    /**
     * @param string $event
     * @param string|int|null $flag
     * @param ...$params
     * @throws QueException
     */
    public function exec(string $event, string|int|null $flag, ...$params)
    {
        if (!$this->has($event)) {
            throw new QueException("Event with key '$event' does not exist.", "Event error",
                HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(2));
        }

        $events = [];
        $executed = [];

        while ($eventItem = $this->cache->lPop($event)) {
            if ($flag && $eventItem['flag'] != $flag) {
                $events[] = $eventItem;
                continue;
            }

            $hash = Hash::sha($eventItem['listener']);

            if (isset($executed[$hash])) continue;

            $runOnce = $eventItem['runOnce'];
            $listener = unserialize($eventItem['listener']);
            $listener(...$params);
            $executed[$hash] = true;
            if (!$runOnce) {
                $events[] = $eventItem;
            }
        }

        foreach (Arr::unique($events, SORT_REGULAR) as $eventItem) {
            $this->push($event, $eventItem['listener'], $eventItem['flag']);
        }
    }

    /**
     * @param string $event
     * @param callable $listener
     * @param string|int|null $flag
     */
    public static function on(string $event, callable $listener, string|int $flag = null)
    {
        $events = self::getInstance();
        $events->push($event, $listener, $flag);
    }

    /**
     * @param string $event
     * @param callable $listener
     * @param string|int|null $flag
     */
    public static function once(string $event, callable $listener, string|int $flag = null)
    {
        $events = self::getInstance();
        $events->push($event, $listener, $flag, true);
    }

    /**
     * @param string $event
     * @param string|int|null $flag
     * @param ...$params
     * @throws QueException
     */
    public static function emit(string $event, string|int|null $flag = null, ...$params)
    {
        $events = self::getInstance();
        $events->exec($event, $flag, ...$params);
    }
}