<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 2/5/2019
 * Time: 11:02 AM
 */

namespace que\session;

use que\session\type\Files;
use que\session\type\QueKip;
use que\session\type\Memcached as Memcached;
use que\session\type\Redis as RedisCache;

class Session
{
    const SESSION_STARTED = true;
    const SESSION_NOT_STARTED = false;
    protected const UPPER = '_ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    protected const LOWER = '-abcdefghijklmnopqrstuvwxyz';

    /**
     * @var bool
     */
    private static bool $sessionState = self::SESSION_NOT_STARTED;

    /**
     * @var array
     */
    protected static array $sessionIDs = [];

    /**
     * @var Session
     */
    private static Session $instance;

    protected function __construct()
    {
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * @return Session
     */
    public static function getInstance(): Session
    {
        if (!isset(self::$instance)) self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param array $options
     * @return bool
     */
    public static function startSession(array $options = [])
    {
        if (self::$sessionState == self::SESSION_NOT_STARTED)
            self::$sessionState = session_start($options);
        return self::$sessionState;
    }

    /**
     * @param string|null $session_id
     * @return string
     */
    public static function getSessionID(string $session_id = null)
    {
        $session_id = $session_id ?: session_id();
        if (isset(self::$sessionIDs[$session_id])) return self::$sessionIDs[$session_id];
        $partition = strtr(config('session.partition', APP_PACKAGE_NAME), self::UPPER, self::LOWER);
        return self::$sessionIDs[$session_id] = "{$partition}-session-id:" . wordwrap($session_id, 4, ":", true);
    }

    /**
     * @return bool
     */
    public function regenerateID(): bool {

        $cache_config = (array) config('cache', []);
        $files = $this->getFiles();
        $queKip = $this->getQueKip();
        $memcached = null;
        $redis = null;

        if (($cache_config['memcached']['enable'] ?? false) === true) $memcached = $this->getMemcached();
        if (($cache_config['redis']['enable'] ?? false) === true) $redis = $this->getRedis();

        $old_session_id = session_id();
        // change session ID for the current session and invalidate old session ID

        if (!($status = session_regenerate_id(true))) {
            // Give it some time to regenerate session ID
            sleep(1);
            $status = session_regenerate_id(true);
        }

        if (!$status) return false;
        unset(self::$sessionIDs[$old_session_id]);
        $files->reset_session_id(self::getSessionID());
        $queKip->reset_session_id(self::getSessionID());
        if ($memcached) $memcached->reset_session_id(self::getSessionID());
        if ($redis) $redis->reset_session_id(self::getSessionID());
        return true;
    }

    /**
     * @return Files
     */
    public function getFiles(): Files
    {
        return Files::getInstance(self::getSessionID());
    }

    /**
     * @return Memcached
     */
    public function getMemcached(): Memcached
    {
        return Memcached::getInstance(self::getSessionID());
    }

    /**
     * @return RedisCache
     */
    public function getRedis(): RedisCache
    {
        return RedisCache::getInstance(self::getSessionID());
    }

    /**
     * @return QueKip
     */
    public function getQueKip(): QueKip
    {
        return QueKip::getInstance(self::getSessionID());
    }

    /**
     * @return bool
     */
    public function destroy()
    {
        if (self::$sessionState == self::SESSION_STARTED) {

            $cache_config = (array) config('cache', []);
            $files = $this->getFiles();
            $queKip = $this->getQueKip();
            $memcached = null;
            $redis = null;

            if (($cache_config['memcached']['enable'] ?? false) === true) $memcached = $this->getMemcached();
            if (($cache_config['redis']['enable'] ?? false) === true) $redis = $this->getRedis();

            $files->session_destroy();
            $queKip->session_destroy();
            if ($memcached) $memcached->session_destroy();
            if ($redis) $redis->session_destroy();

            $this->regenerateID();

            return self::$sessionState = self::SESSION_NOT_STARTED;
        }

        return false;
    }

}