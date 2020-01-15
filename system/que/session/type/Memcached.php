<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 3/17/2019
 * Time: 10:44 PM
 */

namespace que\session\type;

use que\common\exception\QueRuntimeException;

class Memcached
{
    /**
     * @var string
     */
    private $session_id;

    /**
     * @var Memcached
     */
    private static $instance;

    /**
     * @var \Memcache
     */
    private $memcached;

    /**
     * @var array
     */
    private $data = [];

    /**
     * Memcache constructor.
     * @param string $session_id
     */
    protected function __construct(string $session_id)
    {
        $this->session_id = $session_id;

        if (!isset($this->memcached)) {

            $this->memcached = new \Memcache();

            $host = CONFIG['session']['memcached']['host'] ?? "127.0.0.1";
            $port = CONFIG['session']['memcached']['port'] ?? 11211;

            if (!$this->memcached->addserver($host, $port))
                throw new QueRuntimeException("Unable to connect to memcached", "Session Error", E_USER_ERROR);
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
     * @return Memcached
     */
    public static function getInstance(string $session_id): Memcached
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
        return $this->memcached->set($this->session_id, $this->data, null, $expire);
    }

    /**
     * @param $key
     * @return bool
     */
    public function delete($key): bool {
        unset($this->data[$key]);
        return $this->memcached->set($this->session_id, $this->data);
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
        $this->memcached->delete($this->session_id);
        $this->memcached->set($this->session_id = $session_id, $this->data);
        $this->fetch_data();
        return $this->session_id;
    }

    private function fetch_data() {
        $this->data = $this->memcached->get($this->session_id);
    }

}