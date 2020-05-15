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
    private static $enabled = [
        'memcached' => false,
        'redis' => false
    ];

    /**
     * State constructor.
     */
    public function __construct()
    {
        self::$enabled['memcached'] = config('cache.memcached.enable', false);
        self::$enabled['redis'] = config('cache.redis.enable', false);
    }

    /**
     * @param array $state
     */
    protected static function set_state(array $state): void
    {

        if (!isset($state['uid'])) throw new QueRuntimeException(
            "Trying to set state without a 'uid' key. Your state must have a unique id",
            "State Error", E_USER_ERROR, 0, PreviousException::getInstance());

        Session::getInstance()->getFiles()->_get()['session']['user'] = $state;

        self::$state['files'] = &Session::getInstance()->getFiles()->_get()['session']['user'];

        if (self::$enabled['memcached'] === true) {
            ($memcached = Session::getInstance()->getMemcached())->set('user', $state);
            self::$state['memcached'] = $memcached->get('user');
        }

        if (self::$enabled['redis'] === true) {
            ($redis = Session::getInstance()->getRedis())->set('user', $state);
            self::$state['redis'] = $redis->get('user');
        }

        if (self::$enabled['memcached'] !== true && self::$enabled['redis'] !== true) {
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
        unset(Session::getInstance()->getFiles()->_get()['session']);
        if (self::$enabled['memcached'] === true) Session::getInstance()->getMemcached()->delete('user');
        if (self::$enabled['redis'] === true) Session::getInstance()->getRedis()->del('user');
        if (self::$enabled['memcached'] !== true && self::$enabled['redis'] !== true)
            Session::getInstance()->getQueKip()->unset('user');

        self::$state['files'] = self::$state['memcached'] = self::$state['redis'] = self::$state['quekip'] = [];
    }

    private static function resolve_state(): void
    {

        if (!empty(self::$state['files']) &&
            (
                (
                    (self::$enabled['memcached'] === true && !empty(self::$state['memcached'])) ||
                    (self::$enabled['redis'] === true && !empty(self::$state['redis']))
                ) || (
                    (self::$enabled['memcached'] !== true && self::$enabled['redis'] !== true) &&
                    !empty(self::$state['quekip'])
                )
            )
        ) return;

        $memcached = $redis = $quekip = null;

        if (self::$enabled['memcached'] === true) {
            $memcached = Session::getInstance()->getMemcached();
        }

        if (self::$enabled['redis'] === true) {
            $redis = Session::getInstance()->getRedis();
        }

        if (self::$enabled['memcached'] !== true && self::$enabled['redis'] !== true)
            $quekip = Session::getInstance()->getQueKip();

        if (!isset(Session::getInstance()->getFiles()->_get()['session']['user'])) {

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

            Session::getInstance()->getFiles()->_get()['session']['user'] = $user;

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

        if (!(
            isset(self::$state['files']['uid']) &&
            (
                (
                    (self::$enabled['memcached'] === true && isset(self::$state['memcached']['uid'])) ||
                    (self::$enabled['redis'] === true && isset(self::$state['redis']['uid']))
                ) || (
                    (self::$enabled['memcached'] !== true && self::$enabled['redis'] !== true) &&
                    isset(self::$state['quekip']['uid'])
                )
            )
        )) return false;

        if (self::$enabled['memcached'] === true && self::$enabled['redis'] === true) {

            return (self::$state['files']['uid'] == self::$state['memcached']['uid'] &&
                self::$state['files']['uid'] == self::$state['redis']['uid']);
        }

        if (self::$enabled['memcached'] === true) {

            return (self::$state['files']['uid'] == self::$state['memcached']['uid']);
        }

        if (self::$enabled['redis'] === true) {

            return (self::$state['files']['uid'] == self::$state['redis']['uid']);
        }

        if (isset(self::$state['quekip']['uid'])) {

            return (self::$state['files']['uid'] == self::$state['quekip']['uid']);
        }

        return false;
    }

    /**
     * @return bool
     */
    protected static function has_active_state(): bool
    {
        return Session::getInstance()->getFiles()->get(['session' => 'user']) ||
            (self::$enabled['memcached'] === true && Session::getInstance()->getMemcached()->get('user')) ||
            (self::$enabled['redis'] === true && Session::getInstance()->getRedis()->get('user')) ||
            ((self::$enabled['memcached'] !== true && self::$enabled['redis'] !== true) &&
                Session::getInstance()->getQueKip()->get('user'));
    }
}