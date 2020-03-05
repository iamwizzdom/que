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

class Redis
{
    private $session_id;

    /**
     * @var Redis
     */
    private static $instance;

    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @var array
     */
    private $data = [];

    protected function __construct($session_id)
    {
        $this->session_id = $session_id;

        if (!isset($this->redis)) {

            $host = CONFIG['session']['redis']['host'] ?? "127.0.0.1";
            $port = CONFIG['session']['redis']['port'] ?? 6379;
            $enable = CONFIG['session']['redis']['enable'] ?? false;

            if (!$enable)
                throw new QueRuntimeException("Can't use redis, redis is disabled from config", "Session Error",
                    E_USER_ERROR, 0, PreviousException::getInstance(2));

            $this->redis = new \Redis();

            if (!$this->redis->connect($host, $port))
                throw new QueRuntimeException("Unable to connect to redis", "Session Error",
                    E_USER_ERROR, 0, PreviousException::getInstance(2));

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
     * @param $key
     * @return mixed|null
     */
    public function get($key) {
        return $this->data[$key] ?? null;
    }

    /**
     * @param $key
     * @param $value
     * @param int|null $expire
     * @return bool
     */
    public function set($key, $value, int $expire = null): bool {
        $this->data[$key] = $value;
        return $this->redis->set($this->session_id, json_encode($this->data, JSON_PRETTY_PRINT), $expire);
    }

    /**
     * @param mixed ...$keys
     * @return int
     */
    public function del(...$keys): int {
        $count = 0;
        foreach ($keys as $key) {
            unset($this->data[$key]);
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
        $this->data = $this->redis->get($this->session_id);
        $this->data = !empty($this->data) && is_string($this->data) ? json_decode($this->data, true) : [];
    }

}