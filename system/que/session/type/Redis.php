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
use que\http\HTTP;
use que\support\Arr;

class Redis
{
    /**
     * @var string
     */
    private string $session_id;

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
    private array $pointer = [];

    protected function __construct(?string $session_id)
    {
        $this->session_id = $session_id ?: 'cache';

        if (!isset($this->redis)) {

            $this->host = config('cache.redis.host', "127.0.0.1");
            $this->port = config('cache.redis.port', 6379);
            $this->enable = (bool) config('cache.redis.enable', false);
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
     * @param string|null $session_id
     * @return Redis
     */
    public static function getInstance(string $session_id = null): Redis
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

        if (!$this->enable) throw new QueRuntimeException("Can't use redis, redis is disabled from config.",
            "Redis Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(4));

        if (class_exists(\Redis::class)) $this->redis = new \Redis();
        else throw new QueRuntimeException("Redis is not installed on this server.", "Redis Error",
            E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(4));

        if (!$this->redis->connect($this->host, $this->port))
            throw new QueRuntimeException("Unable to connect to redis.", "Redis Error",
                E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(4));
    }

    /**
     * @param $key
     * @return bool
     */
    public function isset($key): bool {
        return Arr::isset($this->pointer, $key) || $this->redis->exists($key);
    }

    /**
     * @param $key
     * @param null $default
     * @return array|mixed
     */
    public function get($key, $default = null) {
        $data = Arr::get($this->pointer, $key, $default);
        if ($data == $default) return $data;
        if (isset($data['expire']) && is_numeric($data['expire']) && APP_TIME > (int) $data['expire']) {
            $this->del($key);
            return $default;
        }
        return $data['data'] ?? $default;
    }

    /**
     * @param $key
     * @param $value
     * @param int|null $expire
     * @return bool
     */
    public function set($key, $value, int $expire = null): bool {
        Arr::set($this->pointer, $key, [
            'data' => $value,
            'expire' => $expire !== null ? (APP_TIME + $expire) : null
        ]);
        if (!($status = $this->write_data())) Arr::unset($this->pointer, $key);
        return $status;
    }

    /**
     * @param $key
     * @param ...$values
     * @return false|int
     */
    public function rPush($key, ...$values) {
        return $this->redis->rPush($key, ...$values);
    }

    /**
     * @param $key
     * @param ...$values
     * @return false|int
     */
    public function lPush($key, ...$values) {
        return $this->redis->lPush($key, ...$values);
    }

    /**
     * @param $key
     * @return bool|mixed
     */
    public function rPop($key) {
        return $this->redis->rPop($key);
    }

    /**
     * @param $key
     * @return bool|mixed
     */
    public function lPop($key) {
        return $this->redis->lPop($key);
    }

    /**
     * @param mixed ...$keys
     * @return int
     */
    public function del(...$keys): int {
        $count = 0;
        foreach ($keys as $key) {
            if (Arr::isset($this->pointer, $key)) {
                Arr::unset($this->pointer, $key);
                $count++;
            }
        }
        if ($count > 0) $this->write_data();
        else return $this->redis->del(...$keys);
        return $count;
    }

    /**
     * This method is used to switch the session or retrieve the current session id.
     * The session will be switched if a new session id is passed
     * with the $session_id param, while the current session id
     * will be returned if no session id is passed
     *
     * @param string|null $session_id
     * @return string
     */
    public function session_id(string $session_id = null) {
        if (is_null($session_id)) return $this->session_id;
        if ($this->session_id == $session_id) return $this->session_id;
        $this->session_id = $session_id;
        $this->fetch_data();
        return $this->session_id;
    }

    /**
     * This method is used to reset the current session id.
     *
     * @param string $session_id
     * @return string
     */
    public function reset_session_id(string $session_id) {
        if ($this->session_id == $session_id) return $this->session_id;
        $old_pointer = $this->pointer;
        $this->session_destroy();
        $this->pointer = $old_pointer;
        $this->session_id = $session_id;
        $this->write_data();
        return $this->session_id;
    }

    /**
     * This method will destroy the current session
     */
    public function session_destroy() {
        if ($this->redis->del($this->session_id)){
            $this->pointer = [];
            return true;
        }
        return false;
    }

    private function fetch_data() {
        $data = $this->redis->get($this->session_id);
        $this->pointer = !empty($data) && is_string($data) ? json_decode($data, true) : [];
    }

    /**
     * @return bool
     */
    private function write_data(): bool {
        return $this->redis->set($this->session_id, json_encode($this->pointer),
            config('session.timeout', false) ? config('session.timeout_time') : null);
    }

}