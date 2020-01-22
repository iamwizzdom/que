<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 2/1/2019
 * Time: 10:36 PM
 */

namespace que\user;


use ArrayAccess;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\model\Model;
use que\session\Session;
use que\utility\client\Browser;
use que\utility\client\IP;

class User extends State implements ArrayAccess
{
    /**
     * @var User
     */
    private static $instance;

    /**
     * @var array
     */
    private static $state;

    /**
     * @var object
     */
    private static $user;

    /**
     * @var Model
     */
    private static $model;

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
     * @return User
     */
    public static function getInstance(): User
    {

        if (!self::isLoggedIn())
            throw new QueRuntimeException("Trying to get a user instance when you're not logged in.",
                "User Error", E_USER_ERROR, 0, PreviousException::getInstance(debug_backtrace(), 1));

        if (!isset(self::$instance)) {

            self::$state = self::get_state();

            if (!self::is_equal_state()) {
                self::logout(vsprintf("Your connection state got corrupted due to access from (IP::%s) using " .
                    "%s browser at %s with %s system. Please re-login or change password to avoid possible hijack", [
                    self::getLastIP(),
                    self::getLastBrowser()['browser'] ?? 'unknown',
                    date("h:i a l, jS M Y", self::getLastSeen()) ?: 'unknown',
                    self::getLastBrowser()['platform'] ?? 'unknown'
                ]));
            }

            if (SESSION_TIMEOUT === true && (APP_TIME >= (self::getLastSeen() + SESSION_TIMEOUT_TIME)))
                self::logout(vsprintf("System session expired for security reasons. Please re-login (IP::%s)", [self::getLastIP()]));

            self::$user = &self::$state['data'];

            if (SESSION_REGENERATION === true && ((APP_TIME - self::getLastSeen()) >= SESSION_REGENERATION_TIME))
                self::regenerate();

            self::updateState();

            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        if (!isset(self::$model))
            self::$model = new Model(self::$user, (CONFIG['db_table']['user']['name'] ?? 'users'));
        return self::$model;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getValue($key, $default = null)
    {
        return self::$user->{$key} ?? $default;
    }

    /**
     * @param $key
     * @param null $default
     * @return int
     */
    public function getInt($key, $default = null)
    {
        return (int) $this->getValue($key, $default);
    }

    /**
     * @param $key
     * @param null $default
     * @return float
     */
    public function getFloat($key, $default = null)
    {
        return (float) $this->getValue($key, $default);
    }

    /**
     * @return object
     */
    public function &getUserObject(): object
    {
        return self::$user;
    }

    /**
     * @return array
     */
    public function getUserArray(): array
    {
        return object_to_array(self::$user);
    }

    /**
     * @param array $columns
     * @return bool
     */
    public function update(array $columns)
    {
        if (!self::isLoggedIn() || empty($columns)) return false;

        $columnsToUpdate = [];
        foreach ($columns as $key => $value) {
            if (isset(self::$user->{$key}) && self::$user->{$key} != $value) {
                $columnsToUpdate[$key] = $value;
            }
        }

        if (empty($columnsToUpdate)) return false;

        $primaryKey = (CONFIG['db_table']['user']['primary_key'] ?? 'id');

        $update = db()->update((CONFIG['db_table']['user']['name'] ?? 'users'), $columnsToUpdate, [
            'AND' => [
                $primaryKey => $this->getValue($primaryKey, 0)
            ]
        ]);

        if ($status = $update->isSuccessful()) {
            foreach ($columnsToUpdate as $key => $value)
                self::$user->{$key} = $value;

            self::updateState();
        }

        return $status;
    }

    /**
     * @param $key
     * @param $value
     * @return bool
     */
    public function updateInMemory($key, $value) {
        if (!isset(self::$user->{$key}) || self::$user->{$key} == $value) return false;
        self::$user->{$key} = $value;
        self::updateState();
        return true;
    }

    /**
     * Update user state
     */
    private static function updateState()
    {
        self::login(self::$user);
    }

    /**
     * @return mixed
     */
    private static function getLastSeen()
    {
        return self::$state['time'] ?? APP_TIME;
    }

    /**
     * @return mixed
     */
    private static function getLastIP()
    {
        return self::$state['ip'] ?? 'unknown';
    }

    /**
     * @return mixed
     */
    private static function getLastBrowser()
    {
        return self::$state['browser'] ?? [];
    }

    private static function regenerate()
    {

        $memcached = Session::getInstance()->getMemcached();
        $redis = Session::getInstance()->getRedis();

        if (!@session_regenerate_id(true)) { // change session ID for the current session and invalidate old session ID
            // Give it some time to regenerate session ID
            sleep(1);
            session_regenerate_id(true);
        }

        $primaryKey = (CONFIG['db_table']['user']['primary_key'] ?? 'id');

        $user = db()->find((CONFIG['db_table']['user']['name'] ?? 'users'), $primaryKey,
            self::$state['data']->{$primaryKey} ?? 0);

        if ($user->isSuccessful()) {
            $userData = $user->getQueryResponseArray(0);
            foreach ($userData as $key => $value)
                self::$user->{$key} = $value;
        }

        $memcached->reset_session_id(Session::getSessionID());
        $redis->reset_session_id(Session::getSessionID());
        self::updateState();
    }


    /**
     * @return bool
     */
    public static function isLoggedIn(): bool
    {
        return self::has_active_state();
    }

    /**
     * Log in user
     * @param object $user
     */
    public static function login(object $user)
    {

        self::set_state([
            'uid' => Session::getSessionID(),
            'data' => $user,
            'time' => APP_TIME,
            'ip' => IP::real(),
            'browser' => Browser::browserInfo()
        ]);
    }

    /**
     * Log out user
     * @param string $redirect_to
     * @param null $message
     */
    public static function logout($message = null, string $redirect_to = null)
    {

        $redirect_to = $redirect_to ?? (current_route()->isRequireLogIn() ? APP_HOME_PAGE : current_uri());
        $message = $message ?? sprintf("Good bye, see you soon. Log-out successful (IP::%s)", self::getLastIP());
        self::flush();
        if (current_route()->getType() != 'web') throw new QueRuntimeException($message, "User Error", E_USER_ERROR,
            0, PreviousException::getInstance(debug_backtrace()));
        else http()->redirect()->setUrl($redirect_to)->setHeader($message, SUCCESS)->initiate();

    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
        return isset(self::$user->{$offset});
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
        return self::$user->{$offset} ?? null;
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
        self::$user->{$offset} = $value;

    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
        unset(self::$user->{$offset});
    }
}