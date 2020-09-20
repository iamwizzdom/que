<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/11/2019
 * Time: 9:10 AM
 */

namespace que\user;


use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\session\Session;

abstract class State
{

    /**
     * @var array
     */
    private static array $state = [
        'files' => [],
        'memcached' => [],
        'redis' => [],
        'quekip' => []
    ];

    /**
     * @var array
     */
    protected static array $cache_config = [];

    /**
     * @param array $state
     */
    protected static function set_state(array $state): void
    {
        if (empty(self::$cache_config)) self::$cache_config = (array) config('cache', []);

        if (!isset($state['uid'])) throw new QueRuntimeException(
            "Trying to set state without a 'uid' key. Your state must have a unique id",
            "State Error", E_USER_ERROR, 0, PreviousException::getInstance());

        Session::getInstance()->getFiles()->_get()['session']['user'] = $state;

        self::$state['files'] = &Session::getInstance()->getFiles()->_get()['session']['user'];

        if ((self::$cache_config['memcached']['enable'] ?? false) === true) {
            ($memcached = Session::getInstance()->getMemcached())->set('user', $state);
            self::$state['memcached'] = $memcached->get('user');
        }

        if ((self::$cache_config['redis']['enable'] ?? false) === true) {
            ($redis = Session::getInstance()->getRedis())->set('user', $state);
            self::$state['redis'] = $redis->get('user');
        }

        if ((self::$cache_config['memcached']['enable'] ?? false) !== true && (self::$cache_config['redis']['enable'] ?? false) !== true) {
            ($quekip = Session::getInstance()->getQueKip())->set('user', $state);
            self::$state['quekip'] = $quekip->get('user');
        }

    }

    /**
     * @return array|null
     */
    protected static function &get_state(): ?array
    {
        self::resolve_state();
        return self::$state['files'];
    }

    /**
     * @return array
     */
    protected static function &get_state_all(): array
    {
        return self::$state;
    }

    protected static function flush(): void
    {
        if (empty(self::$cache_config))
            self::$cache_config = config('cache', []);

        Session::getInstance()->getFiles()->_unset('session');
        if ((self::$cache_config['memcached']['enable'] ?? false) === true) Session::getInstance()->getMemcached()->delete('user');
        if ((self::$cache_config['redis']['enable'] ?? false) === true) Session::getInstance()->getRedis()->del('user');
        if ((self::$cache_config['memcached']['enable'] ?? false) !== true && (self::$cache_config['redis']['enable'] ?? false) !== true)
            Session::getInstance()->getQueKip()->delete('user');

        self::$state['files'] = self::$state['memcached'] = self::$state['redis'] = self::$state['quekip'] = [];
    }

    private static function resolve_state(): void
    {
        if (empty(self::$cache_config))
            self::$cache_config = config('cache', []);

        if (!empty(self::$state['files']) &&
            (
                (
                    ((self::$cache_config['memcached']['enable'] ?? false) === true && !empty(self::$state['memcached'])) ||
                    ((self::$cache_config['redis']['enable'] ?? false) === true && !empty(self::$state['redis']))
                ) || (
                    ((self::$cache_config['memcached']['enable'] ?? false) !== true && (self::$cache_config['redis']['enable'] ?? false) !== true) &&
                    !empty(self::$state['quekip'])
                )
            )
        ) return;

        $memcached = $redis = $quekip = null;

        if ((self::$cache_config['memcached']['enable'] ?? false) === true) {
            $memcached = Session::getInstance()->getMemcached();
        }

        if ((self::$cache_config['redis']['enable'] ?? false) === true) {
            $redis = Session::getInstance()->getRedis();
        }

        if ((self::$cache_config['memcached']['enable'] ?? false) !== true && (self::$cache_config['redis']['enable'] ?? false) !== true)
            $quekip = Session::getInstance()->getQueKip();

        if (!Session::getInstance()->getFiles()->get('session.user')) {

            $user = null;

            if (!is_null($memcached)) {
                $user = $memcached->get('user');
            }

            if (is_null($user) && !is_null($redis)) {
                $user = $redis->get('user');
            }

            if (is_null($user) && !is_null($quekip)) {
                $user = $quekip->get('user');
            }

            Session::getInstance()->getFiles()->set('session.user', $user);

        }

        self::$state['files'] = &Session::getInstance()->getFiles()->_get()['session']['user'];

        if (!is_null($memcached)) {

            if (!$memcached->get('user')) $memcached->set('user', self::$state['files']);
            self::$state['memcached'] = $memcached->get('user');
        }

        if (!is_null($redis)) {

            if (!$redis->get('user')) $redis->set('user', self::$state['files']);
            self::$state['redis'] = $redis->get('user');
        }

        if (!is_null($quekip)) {

            if (!$quekip->get('user')) $quekip->set('user', self::$state['files']);
            self::$state['quekip'] = $quekip->get('user');
        }
    }

    /**
     * @return bool
     */
    protected static function is_equal_state(): bool
    {
        if (empty(self::$cache_config)) self::$cache_config = config('cache', []);

        if (!(
            !empty((self::$state['files']['uid'] ?? null)) &&
            (
                (
                    ((self::$cache_config['memcached']['enable'] ?? false) === true && isset(self::$state['memcached']['uid'])) ||
                    ((self::$cache_config['redis']['enable'] ?? false) === true && isset(self::$state['redis']['uid']))
                ) || (
                    ((self::$cache_config['memcached']['enable'] ?? false) !== true && (self::$cache_config['redis']['enable'] ?? false) !== true) &&
                    isset(self::$state['quekip']['uid'])
                )
            )
        )) return false;

        if ((self::$cache_config['memcached']['enable'] ?? false) === true &&
            (self::$cache_config['redis']['enable'] ?? false) === true) {

            return (self::$state['files']['uid'] == self::$state['memcached']['uid'] &&
                self::$state['files']['uid'] == self::$state['redis']['uid']);
        }

        if ((self::$cache_config['memcached']['enable'] ?? false) === true) {

            return (self::$state['files']['uid'] == self::$state['memcached']['uid']);
        }

        if ((self::$cache_config['redis']['enable'] ?? false) === true) {

            return (self::$state['files']['uid'] == self::$state['redis']['uid']);
        }

        return (self::$state['files']['uid'] == (self::$state['quekip']['uid'] ?? null));
    }

    /**
     * @return bool
     */
    protected static function has_active_state(): bool
    {
        if (empty(self::$cache_config))
            self::$cache_config = config('cache', []);

        return Session::getInstance()->getFiles()->get('session.user') ||
            ((self::$cache_config['memcached']['enable'] ?? false) === true && Session::getInstance()->getMemcached()->get('user')) ||
            ((self::$cache_config['redis']['enable'] ?? false) === true && Session::getInstance()->getRedis()->get('user')) ||
            (((self::$cache_config['memcached']['enable'] ?? false) !== true && (self::$cache_config['redis']['enable'] ?? false) !== true) &&
                Session::getInstance()->getQueKip()->get('user'));
    }
}