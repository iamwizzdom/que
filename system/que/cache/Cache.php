<?php

namespace que\cache;

use JetBrains\PhpStorm\ExpectedValues;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\http\HTTP;
use que\session\type\Memcached;
use que\session\type\QueKip;
use que\session\type\Redis;

/**
 * Created by PhpStorm.
 * User: wisdom Emenike
 * Date: 20/01/2022
 * Time: 12:19 PM
 */

class Cache
{
    /**
     * @var Cache
     */
    private static Cache $instance;

    /**
     * @var QueKip
     */
    private QueKip $queKip;

    /**
     * @var Redis
     */
    private Redis $redis;

    /**
     * @var Memcached
     */
    private Memcached $memcached;

    private string $using = 'quekip';

    public function __construct()
    {
        $cache_config = (array) config('cache', []);

        if (($cache_config['memcached']['enable'] ?? false) === true) {
            $this->memcached = Memcached::getInstance('cache');
            $this->using = 'memcached';
        }

        if (($cache_config['redis']['enable'] ?? false) === true) {
            $this->redis = Redis::getInstance('cache');
            $this->using = 'redis';
        }

        $this->queKip = QueKip::getInstance('cache', true);
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
     * @return Cache
     */
    public static function getInstance(): Cache
    {
        if (!isset(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * @return string
     */
    public function using(): string
    {
        return $this->using;
    }

    /**
     * @param string $use
     */
    public function use(#[ExpectedValues(['memcached', 'redis', 'quekip'])] string $use) {

        if ($use == 'memcached' && !isset($this->memcached)) {
            throw new QueRuntimeException("Can't use memcached, memcached is disabled from config.",
                "Memcached Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR,
                PreviousException::getInstance(1));
        }

        if ($use == 'redis' && !isset($this->redis)) {
            throw new QueRuntimeException("Can't use redis, redis is disabled from config.",
                "Redis Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR,
                PreviousException::getInstance(1));
        }

        $this->using = $use;
    }

    public function isset($key): bool {
        return match ($this->using) {
            'redis' => $this->redis->isset($key),
            'memcached' => $this->memcached->isset($key),
            default => $this->queKip->isset($key)
        };
    }

    /**
     * @param $key
     * @param null $default
     * @return array|mixed|null
     */
    public function get($key, $default = null) {
        return match ($this->using) {
            'redis' => $this->redis->get($key, $default),
            'memcached' => $this->memcached->get($key, $default),
            default => $this->queKip->get($key, $default)
        };
    }

    /**
     * @param $key
     * @param $value
     * @param int|null $expire
     * @return bool
     */
    public function set($key, $value, int $expire = null): bool {
        return match ($this->using) {
            'redis' => $this->redis->set($key, $value, $expire),
            'memcached' => $this->memcached->set($key, $value, $expire),
            default => $this->queKip->set($key, $value, $expire)
        };
    }

    /**
     * @param $key
     * @param ...$values
     * @return bool|int
     */
    public function rPush($key, ...$values) {
        return match ($this->using) {
            'redis' => $this->redis->rPush($key, ...$values),
            'memcached' => $this->memcached->rPush($key, ...$values),
            default => $this->queKip->rPush($key, ...$values)
        };
    }

    /**
     * @param $key
     * @param ...$values
     * @return bool|int
     */
    public function lPush($key, ...$values) {
        return match ($this->using) {
            'redis' => $this->redis->lPush($key, ...$values),
            'memcached' => $this->memcached->lPush($key, ...$values),
            default => $this->queKip->lPush($key, ...$values)
        };
    }

    /**
     * @param $key
     * @return bool|mixed
     */
    public function rPop($key) {
        return match ($this->using) {
            'redis' => $this->redis->rPop($key),
            'memcached' => $this->memcached->rPop($key),
            default => $this->queKip->rPop($key)
        };
    }

    /**
     * @param $key
     * @return bool|mixed
     */
    public function lPop($key) {
        return match ($this->using) {
            'redis' => $this->redis->lPop($key),
            'memcached' => $this->memcached->lPop($key),
            default => $this->queKip->lPop($key)
        };
    }

    /**
     * @param ...$keys
     * @return int
     */
    public function del(...$keys): int {
        return match ($this->using) {
            'redis' => $this->redis->del(...$keys),
            'memcached' => $this->memcached->delete(...$keys),
            default => $this->queKip->delete(...$keys)
        };
    }
}