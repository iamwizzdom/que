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
use que\http\HTTP;
use que\support\Arr;

class Memcached
{
    /**
     * @var mixed
     */
    private mixed $session_id;

    /**
     * @var mixed
     */
    private mixed $host;

    /**
     * @var mixed
     */
    private mixed $port;

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
    private Memcache|\Memcached $memcached;

    /**
     * @var array
     */
    private array $pointer = [];

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
            $this->enable = (bool) config('cache.memcached.enable', false);
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

        if (!$this->enable) throw new QueRuntimeException("Can't use memcached, memcached is disabled from config.",
                "Memcached Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(4));

        if (class_exists(\Memcached::class)) $this->memcached = new \Memcached();
        elseif (class_exists(Memcache::class)) $this->memcached = new Memcache();
        else throw new QueRuntimeException("Memcached is not installed on this server.", "Memcached Error",
            E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(4));

        if (!$this->memcached->addserver($this->host, $this->port))
            throw new QueRuntimeException("Unable to connect to memcached.", "Memcached Error",
                E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(4));
    }

    /**
     * @param $key
     * @return bool
     */
    public function isset($key): bool {
        return Arr::isset($this->pointer, $key);
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null): mixed
    {
        $data = Arr::get($this->pointer, $key, $default);
        if ($data == $default) return $data;
        if (isset($data['expire']) && is_numeric($data['expire']) && APP_TIME > (int) $data['expire']) {
            $this->delete($key);
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
     * @param mixed ...$keys
     * @return int
     */
    public function delete(...$keys): int {
        $count = 0;
        foreach ($keys as $key) {
            Arr::unset($this->pointer, $key);
            $count++;
        }
        if ($count > 0) $this->write_data();
        return $count;
    }

    /**
     * This method is used to switch the session or retrieve the current session id.
     * The session will be switched if a new session id is passed
     * with the $session_id param, while the current session id
     * will be returned if no session id is passed
     *
     * @param string|null $session_id
     * @return string|null
     */
    public function session_id(string $session_id = null): ?string
    {
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
    public function reset_session_id(string $session_id): string
    {
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
    public function session_destroy(): bool
    {
        if ($this->memcached->delete($this->session_id)){
            $this->pointer = [];
            return true;
        }
        return false;
    }

    private function fetch_data() {
        $this->pointer = $this->memcached->get($this->session_id) ?: [];
    }

    /**
     * @return bool
     */
    private function write_data(): bool {
        if ($this->memcached instanceof \Memcached) {
            return $this->memcached->set($this->session_id, $this->pointer,
                config('session.timeout', false) ? config('session.timeout_time', 0) : 0);
        } else {
            return $this->memcached->set($this->session_id, $this->pointer, null,
                config('session.timeout', false) ? config('session.timeout_time', 0) : 0);
        }
    }

}