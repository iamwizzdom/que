<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 3/17/2019
 * Time: 10:44 PM
 */

namespace que\session\type;

use Memcache;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\support\Arr;

class Memcached
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
     * @var Memcached
     */
    private static Memcached $instance;

    /**
     * @var \Memcached|Memcache
     */
    private $memcached;

    /**
     * @var array
     */
    private array $data = [];

    /**
     * Memcached constructor.
     * @param string $session_id
     */
    protected function __construct(string $session_id)
    {
        $this->session_id = $session_id;

        if (!isset($this->memcached)) {

            $this->host = config('cache.memcached.host', "127.0.0.1");
            $this->port = config('cache.memcached.port', 11211);
            $this->enable = config('cache.memcached.enable', false);
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
     * @return Memcached
     */
    public static function getInstance(string $session_id): Memcached
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
            throw new QueRuntimeException("Can't use memcached, memcached is disabled from config.",
                "Memcached Error", E_USER_ERROR, 0, PreviousException::getInstance(4));

        if (class_exists(\Memcached::class)) $this->memcached = new \Memcached();
        elseif (class_exists(Memcache::class)) $this->memcached = new Memcache();
        else throw new QueRuntimeException("Memcached is not installed on this server.", "Memcached Error",
            E_USER_ERROR, 0, PreviousException::getInstance(4));

        if (!$this->memcached->addserver($this->host, $this->port))
            throw new QueRuntimeException("Unable to connect to memcached.", "Memcached Error",
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
        if ($this->memcached instanceof \Memcached) return $this->memcached->set($this->session_id, $this->data, $expire);
        else return $this->memcached->set($this->session_id, $this->data, null, $expire);
    }

    /**
     * @param $key
     * @return bool
     */
    public function delete($key): bool {
        Arr::unset($this->data, $key);
        if ($this->memcached instanceof \Memcached) return $this->memcached->set($this->session_id, $this->data);
        else return $this->memcached->set($this->session_id, $this->data);
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
        $this->data = $this->memcached->get($this->session_id) ?: [];
    }

}