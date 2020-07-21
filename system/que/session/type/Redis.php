<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 3/17/2019
 * Time: 10:44 PM
 */

namespace que\session\type;

use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\support\Arr;

class Redis
{
    /**
     * @var mixed
     */
    private $session_id;

    /**
     * @var mixed
     */
    private $host;

    /**
     * @var mixed
     */
    private $port;

    /**
     * @var bool
     */
    private bool $enable;

    /**
     * @var Redis
     */
    private static Redis $instance;

    /**
     * @var \Redis
     */
    private \Redis $redis;

    /**
     * @var array
     */
    private array $data = [];

    protected function __construct($session_id)
    {
        $this->session_id = $session_id;

        if (!isset($this->redis)) {

            $this->host = config('cache.redis.host', "127.0.0.1");
            $this->port = config('cache.redis.port', 6379);
            $this->enable = config('cache.redis.enable', false);
            $this->connect();
        }

        $this->fetch_data();
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
     * @param string $session_id
     * @return Redis
     */
    public static function getInstance(string $session_id): Redis
    {
        if (!isset(self::$instance))
            self::$instance = new self($session_id);

        return self::$instance;
    }

    /**
     * This method will enable Redis for Que
     */
    public function enable()
    {
        $this->enable = true;
    }

    /**
     * This method will disable Redis for Que
     */
    public function disable()
    {
        $this->enable = false;
    }

    /**
     * @param null $host
     * @param null $port
     */
    private function reconnect($host = null, $port = null) {
        if ($host !== null) $this->host = $host;
        if ($port !== null) $this->port = $port;
        $this->connect();
    }

    private function connect() {

        if (!$this->enable)
            throw new QueRuntimeException("Can't use redis, redis is disabled from config.", "Redis Error",
                E_USER_ERROR, 0, PreviousException::getInstance(4));

        if (class_exists(\Redis::class)) $this->redis = new \Redis();
        else throw new QueRuntimeException("Redis is not installed on this server.", "Redis Error",
            E_USER_ERROR, 0, PreviousException::getInstance(4));

        if (!$this->redis->connect($this->host, $this->port))
            throw new QueRuntimeException("Unable to connect to redis.", "Redis Error",
                E_USER_ERROR, 0, PreviousException::getInstance(4));
    }

    /**
     * @param $key
     * @return bool
     */
    public function isset($key): bool {
        return Arr::isset($this->data, $key);
    }

    /**
     * @param $key
     * @param null $default
     * @return array|mixed
     */
    public function get($key, $default = null) {
        return Arr::get($this->data, $key, $default);
    }

    /**
     * @param $key
     * @param $value
     * @param int|null $expire
     * @return bool
     */
    public function set($key, $value, int $expire = null): bool {
        Arr::set($this->data, $key, $value);
        return $this->redis->set($this->session_id, json_encode($this->data, JSON_PRETTY_PRINT), $expire);
    }

    /**
     * @param mixed ...$keys
     * @return int
     */
    public function del(...$keys): int {
        $count = 0;
        foreach ($keys as $key) {
            Arr::unset($this->data, $key);
            $count++;
        }
        $this->redis->del($this->session_id, json_encode($this->data, JSON_PRETTY_PRINT));
        return $count;
    }

    /**
     * @param null $session_id
     * @return string|null
     */
    public function session_id($session_id = null) {
        if (is_null($session_id)) return $this->session_id;
        else {
            $this->session_id = $session_id;
            $this->fetch_data();
            return $this->session_id;
        }
    }

    /**
     * @param null $session_id
     * @return string
     */
    public function reset_session_id($session_id = null) {
        $this->redis->del($this->session_id);
        $this->redis->set($this->session_id = $session_id, json_encode($this->data, JSON_PRETTY_PRINT));
        $this->fetch_data();
        return $this->session_id;
    }

    private function fetch_data() {
        $data = $this->redis->get($this->session_id);
        $this->data = !empty($data) && is_string($data) ? json_decode($data, true) : [];
    }

}