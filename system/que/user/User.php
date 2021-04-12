<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 2/1/2019
 * Time: 10:36 PM
 */

namespace que\user;


use ArrayAccess;
use Exception;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\database\interfaces\model\Model;
use que\database\model\ModelQueryResponse;
use que\http\HTTP;
use que\session\Session;
use que\support\interfaces\QueArrayAccess;
use que\utility\client\Browser;
use que\utility\client\IP;
use Traversable;

class User implements QueArrayAccess
{
    use State;

    /**
     * @var User
     */
    private static User $instance;

    /**
     * @var Model|null
     */
    private static ?Model $model = null;

    /**
     * @var array|null
     */
    private static ?array $state = null;

    /**
     * User constructor.
     * @param bool $update
     */
    protected function __construct(bool $update)
    {
        if ($update) self::updateState();
    }

    protected function __clone(): void
    {
        // TODO: Implement __clone() method.
        self::$instance = clone self::$instance;
        self::$model = clone self::$model;
    }

    /**
     * @return User
     */
    public static function getInstance(): User
    {

        if (!self::isLoggedIn()) throw new QueRuntimeException("Trying to get a user instance when you're not logged in.",
                "User Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        $session_config = (array) config('session', []);

        if (!isset(self::$instance)) {

            $updateState = self::$state === null;
            self::$state ??= ((array) self::get_state() ?: []);

            if (!self::is_equal_state()) {
                self::logout(vsprintf("Your connection state got corrupted due to access from (IP::%s) using " .
                    "%s browser at %s with %s system. Please re-login or change password to avoid possible hijack", [
                    self::getLastIP(),
                    self::getLastBrowser()['browser'] ?? 'unknown',
                    date("h:i a l, jS M Y", self::getLastSeen()) ?: 'unknown',
                    self::getLastBrowser()['platform'] ?? 'unknown'
                ]));
            }

            if ($session_config['timeout'] === true && (APP_TIME >= (self::getLastSeen() + $session_config['timeout_time'])))
                self::logout(vsprintf("System session expired for security reasons. Please re-login (IP::%s)", [self::getLastIP()]));

            if ($session_config['regeneration'] === true && ((APP_TIME - self::getLastSeen()) >= $session_config['regeneration_time']))
                self::regenerate();

            $provider = self::$state['provider'] ?? config('auth.default.provider');
            $model = \model(config("auth.providers.{$provider}"));

            if ($model && ($implements = class_implements($model)) && in_array(Model::class, $implements)) {
                self::$model = new $model(self::$state['data'], self::$state['table'], self::$state['primaryKey']);
            }

            if (self::$model === null) throw new QueRuntimeException("Trying to get a user instance with an invalid auth provider. Check your auth config to fix this",
                "User Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

            self::$instance = new self($updateState);
        }

        return self::$instance;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return self::$model->getTable();
    }

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return self::$model->getPrimaryKey();
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function getValue($key, $default = null): mixed
    {
        return self::$model->getValue($key, $default);
    }

    /**
     * @param bool $onlyFillable
     * @return array
     */
    public function getArray(bool $onlyFillable = false): array
    {
        return self::$model->getArray($onlyFillable);
    }

    /**
     * @param bool $onlyFillable
     * @return object
     */
    public function &getObject(bool $onlyFillable = false): object
    {
        return self::$model->getObject($onlyFillable);
    }

    /**
     * @return bool
     */
    public function refresh(): bool
    {
        $status = self::$model->refresh();
        if ($status) self::updateState();
        return $status;
    }

    /**
     * @param string|null $primaryKey
     * @return ModelQueryResponse|null
     */
    public function delete(string $primaryKey = null): ?ModelQueryResponse
    {
        $response = self::$model->delete($primaryKey ?: self::$state['primaryKey']);
        if ($response->isSuccessful()) self::logout(sprintf("User account deleted. Good bye. Log-out successful (IP::%s)", self::getLastIP()));
        return $response;
    }

    /**
     * @param array $columns
     * @param string|null $primaryKey
     * @return ModelQueryResponse|null
     */
    public function update(array $columns, string $primaryKey = null): ?ModelQueryResponse
    {
        $response = self::$model->update($columns, $primaryKey ?: self::$model->getPrimaryKey());
        if ($response->isSuccessful()) self::updateState();
        return $response; // TODO: Change the autogenerated stub
    }

    /**
     * @param $key
     * @param $value
     * @return bool
     */
    public function updateInMemory($key, $value): bool {
        if (!self::$model->has($key) || self::$model->validate($key)->isEqual($value)) return false;
        self::$model->set($key, $value);
        self::updateState();
        return true;
    }

    /**
     * Update user state
     */
    private static function updateState()
    {
        self::login(self::$model->getObject());
    }

    /**
     * @return mixed
     */
    private static function getLastSeen(): mixed
    {
        return self::$state['time'] ?? APP_TIME;
    }

    /**
     * @return mixed
     */
    private static function getLastIP(): mixed
    {
        return self::$state['ip'] ?? 'unknown';
    }

    /**
     * @return mixed
     */
    private static function getLastBrowser(): mixed
    {
        return self::$state['browser'] ?? [];
    }

    private static function regenerate()
    {
        Session::getInstance()->regenerateID();
        self::$model->refresh();
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
     *
     * @param object $user
     * @param string|null $provider
     * @param string|null $table
     * @param string|null $primaryKey
     */
    public static function login(object $user, string $provider = null, string $table = null, string $primaryKey = null)
    {
        $provider ??= config('auth.default.provider');

        $model = \model(config("auth.providers.{$provider}") ?: '');

        $database_config = (array) config('database', []);
        $table ??= ($database_config['tables']['user']['name'] ?? 'users');
        $primaryKey ??= ($database_config['tables']['user']['primary_key'] ?? 'id');

        if ($model && ($implements = class_implements($model)) && in_array(Model::class, $implements)) {
            self::$model = new $model($user, $table, $primaryKey);
        }

        if (self::$model === null) throw new QueRuntimeException("Trying to login with an invalid auth provider. Check your auth config to fix this",
            "User Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        self::set_state(self::$state = [
            'uid' => Session::getSessionID(),
            'data' => self::$model->getObject(),
            'table' => $table,
            'primaryKey' => $primaryKey,
            'provider' => $provider,
            'time' => APP_TIME,
            'ip' => IP::real(),
            'browser' => Browser::browserInfo()
        ]);
    }

    /**
     * Log out user
     * @param null $message
     * @param string|null $redirect_to
     */
    public static function logout($message = null, string $redirect_to = null)
    {
        $redirect_to = $redirect_to ?? (current_route()->isRequireLogin() ? current_route()->getRedirectUrl() : current_uri());
        $message = $message ?? sprintf("Good bye, see you soon. Log-out successful (IP::%s)", self::getLastIP());
        self::flush();
        if (current_route()->getType() != 'web') throw new QueRuntimeException($message, "User Error",
            E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance());
        else http()->redirect()->setUrl($redirect_to ?? '/')->setHeader($message, SUCCESS)->initiate();
    }

    public function __get(string $name)
    {
        // TODO: Implement __get() method.
        return self::$model->{$name};
    }

    public function __set(string $name, $value): void
    {
        // TODO: Implement __set() method.
        self::$model->{$name} = $value;
    }

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement __call() method.
        return self::$model->{$name}(...$arguments);
    }

    public function getIterator()
    {
        // TODO: Implement getIterator() method.
        return self::$model->getIterator();
    }

    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
        return self::$model->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
        return self::$model->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
        self::$model->offsetSet($offset, $value);
        self::updateState();
    }

    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
        self::$model->offsetUnset($offset);
        self::updateState();
    }

    public function serialize()
    {
        // TODO: Implement serialize() method.
        return self::$model->serialize();
    }

    public function unserialize($serialized)
    {
        // TODO: Implement unserialize() method.
        self::$model->unserialize($serialized);
        self::updateState();
    }

    public function count()
    {
        // TODO: Implement count() method.
        return self::$model->count();
    }

    public function array_keys(): array
    {
        // TODO: Implement array_keys() method.
        return self::$model->array_keys();
    }

    public function array_values(): array
    {
        // TODO: Implement array_values() method.
        return self::$model->array_values();
    }

    public function key(): int|string|null
    {
        // TODO: Implement key() method.
        return self::$model->key();
    }

    public function current(): mixed
    {
        // TODO: Implement current() method.
        return self::$model->current();
    }

    public function shuffle(): void
    {
        // TODO: Implement shuffle() method.
    }

    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
        return self::$model->jsonSerialize();
    }
}
