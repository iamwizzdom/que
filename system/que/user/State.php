<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/11/2019
 * Time: 9:10 AM
 */

namespace que\user;


use que\common\exception\QueRuntimeException;
use que\session\Session;

abstract class State
{
    /**
     * @var array
     */
    private static $state = [
        'files' => [],
        'memcached' => [],
        'redis' => []
    ];

    /**
     * @param array $state
     */
    protected static function set_state(array $state): void {

        if (!isset($state['uid'])) throw new QueRuntimeException(
            "Trying to set state without a 'uid' key. Your state must have a unique id", "State Error");

        Session::getInstance()->getFiles()->_get()['session']['user'] = $state;
        ($memcached = Session::getInstance()->getMemcached())->set('user', $state);
        ($redis = Session::getInstance()->getRedis())->set('user', $state);

        self::$state['files'] = &Session::getInstance()->getFiles()->_get()['session']['user'];
        self::$state['memcached'] = $memcached->get('user');
        self::$state['redis'] = $redis->get('user');

    }

    /**
     * @return array|null
     */
    protected static function &get_state(): ?array {
        self::resolve_state();
        return self::$state['files'];
    }

    /**
     * @return array
     */
    protected static function &get_state_all(): array {
        return self::$state;
    }

    protected static function flush(): void {
        unset(Session::getInstance()->getFiles()->_get()['session']);
        Session::getInstance()->getMemcached()->delete('user');
        Session::getInstance()->getRedis()->del('user');
        self::$state['files'] = self::$state['memcached'] = self::$state['redis'] = [];
    }

    private static function resolve_state(): void {

        if (!empty(self::$state['files']) && !empty(self::$state['memcached']) && !empty(self::$state['redis'])) return;

        $memcached = Session::getInstance()->getMemcached();
        $redis = Session::getInstance()->getRedis();

        if (!isset(Session::getInstance()->getFiles()->_get()['session']['user'])) {

            Session::getInstance()->getFiles()->_get()['session']['user'] = (
                $memcached->get('user') ?: $redis->get('user')
            );

        }

        self::$state['files'] = &Session::getInstance()->getFiles()->_get()['session']['user'];

        if (!$memcached->get('user')) $memcached->set('user', self::$state['files']);

        self::$state['memcached'] = $memcached->get('user');

        if (!$redis->get('user')) $redis->set('user', self::$state['files']);

        self::$state['redis'] = $redis->get('user');
    }

    /**
     * @return bool
     */
    protected static function is_equal_state(): bool {

        self::resolve_state();

        if (!(isset(self::$state['files']['uid']) &&
            isset(self::$state['memcached']['uid']) &&
            isset(self::$state['redis']['uid'])))
            return false;

        return (self::$state['files']['uid'] == self::$state['memcached']['uid'] &&
            self::$state['files']['uid'] == self::$state['redis']['uid']);
    }

    /**
     * @return bool
     */
    protected static function has_active_state(): bool {
        return Session::getInstance()->getFiles()->get(['session' => 'user']) ||
            Session::getInstance()->getMemcached()->get('user') ||
            Session::getInstance()->getRedis()->get('user');
    }
}