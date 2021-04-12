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
use que\http\HTTP;
use que\session\Session;

trait State
{

    /**
     * @var array
     */
    private static array $cache = [
        'files' => [],
        'memcached' => [],
        'redis' => [],
        'quekip' => []
    ];

    /**
     * @param array $state
     */
    protected static function set_state(array $state): void
    {
        $cache_config = (array)config('cache', []);

        if (!isset($state['uid'])) throw new QueRuntimeException(
            "Trying to set state without a 'uid' key. Your state must have a unique id",
            "State Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance());

        Session::getInstance()->getFiles()->_get()['session']['user'] = $state;

        self::$cache['files'] = &Session::getInstance()->getFiles()->_get()['session']['user'];

        if (($cache_config['memcached']['enable'] ?? false) === true) {
            ($memcached = Session::getInstance()->getMemcached())->set('user', $state);
            self::$cache['memcached'] = $memcached->get('user');
        }

        if (($cache_config['redis']['enable'] ?? false) === true) {
            ($redis = Session::getInstance()->getRedis())->set('user', $state);
            self::$cache['redis'] = $redis->get('user');
        }

        if (($cache_config['memcached']['enable'] ?? false) !== true && ($cache_config['redis']['enable'] ?? false) !== true) {
            ($quekip = Session::getInstance()->getQueKip())->set('user', $state);
            self::$cache['quekip'] = $quekip->get('user');
        }

    }

    /**
     * @return array|null
     */
    protected static function &get_state(): ?array
    {
        self::resolve_state();
        return self::$cache['files'];
    }

    /**
     * @return array
     */
    protected static function &get_state_all(): array
    {
        return self::$cache;
    }

    protected static function flush(): void
    {
        $cache_config = (array)config('cache', []);

        Session::getInstance()->getFiles()->_unset('session');
        if (($cache_config['memcached']['enable'] ?? false) === true) Session::getInstance()->getMemcached()->delete('user');
        if (($cache_config['redis']['enable'] ?? false) === true) Session::getInstance()->getRedis()->del('user');
        if (($cache_config['memcached']['enable'] ?? false) !== true && ($cache_config['redis']['enable'] ?? false) !== true)
            Session::getInstance()->getQueKip()->delete('user');

        self::$cache['files'] = self::$cache['memcached'] = self::$cache['redis'] = self::$cache['quekip'] = [];
    }

    private static function resolve_state(): void
    {
        $cache_config = (array)config('cache', []);

        if (!empty(self::$cache['files']) &&
            (
                (
                    (($cache_config['memcached']['enable'] ?? false) === true && !empty(self::$cache['memcached'])) ||
                    (($cache_config['redis']['enable'] ?? false) === true && !empty(self::$cache['redis']))
                ) || (
                    (($cache_config['memcached']['enable'] ?? false) !== true && ($cache_config['redis']['enable'] ?? false) !== true) &&
                    !empty(self::$cache['quekip'])
                )
            )
        ) return;

        $memcached = $redis = $quekip = null;

        if (($cache_config['memcached']['enable'] ?? false) === true) {
            $memcached = Session::getInstance()->getMemcached();
        }

        if (($cache_config['redis']['enable'] ?? false) === true) {
            $redis = Session::getInstance()->getRedis();
        }

        if (($cache_config['memcached']['enable'] ?? false) !== true && ($cache_config['redis']['enable'] ?? false) !== true)
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

        self::$cache['files'] = &Session::getInstance()->getFiles()->_get()['session']['user'];

        if (!is_null($memcached)) {

            if (!$memcached->get('user')) $memcached->set('user', self::$cache['files']);
            self::$cache['memcached'] = $memcached->get('user');
        }

        if (!is_null($redis)) {

            if (!$redis->get('user')) $redis->set('user', self::$cache['files']);
            self::$cache['redis'] = $redis->get('user');
        }

        if (!is_null($quekip)) {

            if (!$quekip->get('user')) $quekip->set('user', self::$cache['files']);
            self::$cache['quekip'] = $quekip->get('user');
        }
    }

    /**
     * @return bool
     */
    protected static function is_equal_state(): bool
    {
        $cache_config = (array)config('cache', []);

        if (!(
            !empty((self::$cache['files']['uid'] ?? null)) &&
            (
                (
                    (($cache_config['memcached']['enable'] ?? false) === true && isset(self::$cache['memcached']['uid'])) ||
                    (($cache_config['redis']['enable'] ?? false) === true && isset(self::$cache['redis']['uid']))
                ) || (
                    (($cache_config['memcached']['enable'] ?? false) !== true && ($cache_config['redis']['enable'] ?? false) !== true) &&
                    isset(self::$cache['quekip']['uid'])
                )
            )
        )) return false;

        if (($cache_config['memcached']['enable'] ?? false) === true &&
            ($cache_config['redis']['enable'] ?? false) === true) {

            return (self::$cache['files']['uid'] == self::$cache['memcached']['uid'] &&
                self::$cache['files']['uid'] == self::$cache['redis']['uid']);
        }

        if (($cache_config['memcached']['enable'] ?? false) === true) {

            return (self::$cache['files']['uid'] == self::$cache['memcached']['uid']);
        }

        if (($cache_config['redis']['enable'] ?? false) === true) {

            return (self::$cache['files']['uid'] == self::$cache['redis']['uid']);
        }

        return (self::$cache['files']['uid'] == (self::$cache['quekip']['uid'] ?? null));
    }

    /**
     * @return bool
     */
    protected static function has_active_state(): bool
    {
        $cache_config = (array)config('cache', []);

        return Session::getInstance()->getFiles()->get('session.user') ||
            (($cache_config['memcached']['enable'] ?? false) === true && Session::getInstance()->getMemcached()->get('user')) ||
            (($cache_config['redis']['enable'] ?? false) === true && Session::getInstance()->getRedis()->get('user')) ||
            ((($cache_config['memcached']['enable'] ?? false) !== true && ($cache_config['redis']['enable'] ?? false) !== true) &&
                Session::getInstance()->getQueKip()->get('user'));
    }
}