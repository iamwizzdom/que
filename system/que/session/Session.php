<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 2/5/2019
 * Time: 11:02 AM
 */

namespace que\session;

use Memcache;
use que\session\type\Files;
use Redis;
use que\session\type\Memcached as Memcached;
use que\session\type\Redis as RedisCache;

class Session
{

    /**
     * @var Session
     */
    private static $instance;

    protected function __construct()
    {
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    private function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

    /**
     * @return Session
     */
    public static function getInstance(): Session
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param string|null $session_id
     * @return string
     */
    public static function getSessionID(string $session_id = null) {
        return "session-id:" . wordwrap($session_id ?: session_id(), 4, ":", true);
    }

    /**
     * @return Files
     */
    public function getFiles(): Files {
        return Files::getInstance();
    }

    /**
     * @return Memcached
     */
    public function getMemcached(): Memcached {
        return Memcached::getInstance(self::getSessionID());
    }

    /**
     * @return RedisCache
     */
    public function getRedis(): RedisCache {
        return RedisCache::getInstance(self::getSessionID());
    }

}