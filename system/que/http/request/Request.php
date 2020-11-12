<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 3/17/2019
 * Time: 10:44 PM
 */

namespace que\http\request;


use ArrayIterator;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\http\HTTP;
use que\support\Arr;
use que\support\interfaces\QueArrayAccess;
use que\utility\client\IP;
use ReflectionClass;
use Traversable;
use function in_array;

class Request implements QueArrayAccess
{
    /**
     * Que supported HTTP Request Methods
     */
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';

    /**
     * @var bool
     */
    protected static bool $httpMethodOverride = false;

    /**
     * @var string
     */
    protected static ?string $method = null;

    /**
     * @var array
     */
    protected static array $supportedMethods = [];

    /**
     * @var Request
     */
    private static Request $instance;

    /**
     * @var array
     */
    private array $pointer;

    /**
     * Request constructor.
     */
    protected function __construct()
    {
        $this->pointer = &$_REQUEST;
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
     * @return Request
     */
    public static function getInstance(): Request
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * Enables support for the _method request parameter to determine the intended HTTP method.
     *
     * If the HTTP method parameter override is enabled, an incoming "POST" request can be altered
     * and used to send a "PUT" or "DELETE" request via the _method request parameter.
     *
     * The HTTP method can only be overridden when the real HTTP method is POST.
     */
    public static function enableHttpMethodOverride()
    {
        self::$httpMethodOverride = true;
    }

    /**
     * Disables support for the _method request parameter
     */
    public static function disableHttpMethodOverride()
    {
        self::$httpMethodOverride = false;
    }

    /**
     * Checks whether support for the _method request parameter is enabled.
     *
     * @return bool True when the _method request parameter is enabled, false otherwise
     */
    public static function getHttpMethodOverrideStatus()
    {
        return self::$httpMethodOverride;
    }

    /**
     * Sets the request method.
     * @param string $method
     */
    public static function setMethod(string $method)
    {
        if (!self::isSupportedMethod($method))
            throw new QueRuntimeException(sprintf('Unsupported HTTP request method override "%s".', $method),
                "HTTP Request Error", E_USER_ERROR, HTTP::BAD_REQUEST, PreviousException::getInstance(1));
        self::$method = null;
        http()->_server()->set('REQUEST_METHOD', $method);
    }

    /**
     * Gets the request "intended" method.
     *
     * If the X-HTTP-METHOD-OVERRIDE header is set, and if the method is a POST,
     * then it is used to determine the "real" intended HTTP method.
     *
     * The _method request parameter can also be used to determine the HTTP method,
     * but only if @see enableHttpMethodOverride() has been called.
     *
     * The method is always an uppercased string.
     *
     * @return string The request method
     *
     * @see getRealMethod()
     */
    public static function getMethod()
    {
        if (null !== self::$method) return self::$method;

        self::$method = self::getRealMethod();

        if (self::METHOD_POST !== self::$method) return self::$method;

        $method = headers('X-HTTP-METHOD-OVERRIDE');

        if (empty($method) && self::$httpMethodOverride) $method = self::getInstance()->get('_method');

        if (!is_string($method)) return self::$method;

        $method = strtoupper($method);

        if (!in_array($method, [self::METHOD_GET, self::METHOD_POST, self::METHOD_PUT, self::METHOD_PATCH, self::METHOD_DELETE], true)) {
            throw new QueRuntimeException(sprintf('Unsupported HTTP request method override "%s".', $method),
                "HTTP Request Error", E_USER_ERROR, HTTP::BAD_REQUEST, PreviousException::getInstance(1));
        }

        return self::$method = $method;
    }

    /**
     * Gets the "real" request method.
     *
     * @return string The request method
     *
     * @see getMethod()
     */
    public static function getRealMethod()
    {
        return strtoupper(server('REQUEST_METHOD', 'GET'));
    }

    /**
     * Checks whether or not the method is supported by Que
     *
     * @param string|null $method
     * @return bool
     */
    public static function isSupportedMethod(string $method = null)
    {
        return in_array(($method !== null ? strtoupper($method) : self::getMethod()), self::getSupportedMethods(), true);
    }

    /**
     * @return array
     */
    private static function getSupportedMethods()
    {
        if (!empty(self::$supportedMethods)) return self::$supportedMethods;
        $const = (new ReflectionClass(self::class))->getConstants();
        return self::$supportedMethods = array_filter($const, function ($key) {
            return str_starts_with($key, "METHOD");
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Returns the clients real IP address
     *
     * @return mixed|null
     */
    public static function getClientIp()
    {
        return IP::real();
    }

    /**
     * Returns the user.
     *
     * @return string|null
     */
    public static function getAuthUser()
    {
        return headers('PHP_AUTH_USER');
    }

    /**
     * Returns the password.
     *
     * @return string|null
     */
    public static function getAuthPassword()
    {
        return headers('PHP_AUTH_PW');
    }

    /**
     * Returns the protocol version.
     *
     * @return string
     */
    public static function getProtocolVersion()
    {
        return server('SERVER_PROTOCOL');
    }

    /**
     * Checks whether the request is secure or not.
     *
     * This method can read the client protocol from the "X-Forwarded-Proto" header
     *
     * The "X-Forwarded-Proto" header must contain the protocol: "https" or "http".
     *
     * @return bool
     */
    public static function isSecure()
    {
        $proto = server("X-Forwarded-Proto");
        if (!empty($proto)) return in_array(strtolower($proto), ['https', 'ssl', '1', 'on'], true);
        $https = server('HTTPS');
        return !empty($https) && 'off' !== strtolower($https);
    }

    /**
     * Returns the host name.
     *
     * This method can read the client host name from the "X-Forwarded-Host" header
     *
     * The "X-Forwarded-Host" header must contain the client host name.
     *
     * @return string
     */
    public static function getHost()
    {
        $host = server('X-Forwarded-Host');

        if (empty($host)) {
            if (!$host = headers('HOST')) {
                if (!$host = server('SERVER_NAME')) {
                    $host = server('SERVER_ADDR', '');
                }
            }
        }

        // trim and remove port number from host
        // host is lowercase as per RFC 952/2181
        $host = strtolower(preg_replace('/:\d+$/', '', trim($host)));

        // as the host can come from the user (HTTP_HOST and depending on the configuration, SERVER_NAME too can come from the user)
        // check that it does not contain forbidden characters (see RFC 952 and RFC 2181)
        // use preg_replace() instead of preg_match() to prevent DoS attacks with long host names
        if ($host && '' !== preg_replace('/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/', '', $host)) {
            throw new QueRuntimeException(sprintf('Invalid Host "%s".', $host), "HTTP Request Error",
                E_USER_ERROR, HTTP::BAD_REQUEST, PreviousException::getInstance(1));
        }

        return $host;
    }

    /**
     * Returns the port on which the request is made.
     *
     * This method can read the client port from the "X-Forwarded-Port" header
     *
     * The "X-Forwarded-Port" header must contain the client port.
     *
     * @return int|string can be a string if fetched from the server bag
     */
    public static function getPort()
    {

        if ($host = headers('X-Forwarded-Port')) {
            GOTO PROCEED;
        } elseif ($host = headers('X-Forwarded-Host')) {
            GOTO PROCEED;
        } elseif (!$host = headers('HOST')) {
            return server('SERVER_PORT');
        }

        PROCEED:

        if ('[' === $host[0]) {
            $pos = strpos($host, ':', strrpos($host, ']'));
        } else {
            $pos = strrpos($host, ':');
        }

        if (false !== $pos && $port = substr($host, $pos + 1)) {
            return (int) $port;
        }

        return 'https' === self::getScheme() ? 443 : 80;
    }

    /**
     * Gets the request's scheme.
     *
     * @return string
     */
    public static function getScheme()
    {
        return self::isSecure() ? 'https' : 'http';
    }

    /**
     * Returns the HTTP host being requested.
     *
     * The port name will be appended to the host if it's non-standard.
     *
     * @return string
     */
    public static function getHttpHost()
    {
        $scheme = self::getScheme();
        $port = self::getPort();

        if (('http' == $scheme && 80 == $port) || ('https' == $scheme && 443 == $port)) {
            return self::getHost();
        }

        return self::getHost() . ":{$port}";
    }

    /**
     * Returns the request URI
     *
     * This method return the URI from your project root,
     * ignoring any uri before the root.
     *
     * To get the full URI at all times
     * @see getUriOriginal()
     *
     * @return array|mixed|null
     */
    public static function getUri()
    {
        return server('REQUEST_URI');
    }

    /**
     * Returns the full URI at all times
     *
     * @return array|mixed|null
     */
    public static function getUriOriginal()
    {
        return server('REQUEST_URI_ORIGINAL');
    }

    /**
     * Returns all definded URI params if any
     * @return array|null
     */
    public static function getUriParams()
    {
        return server('route.params');
    }

    /**
     * Returns a definded URI param
     * @param string $key
     * @return mixed|null
     */
    public static function getUriParam(string $key)
    {
        return server("route.params.{$key}");
    }

    /**
     * @param $offset
     * @param $value
     */
    public function set($offset, $value) {
        Arr::set($this->pointer, $offset, $value);
    }

    /**
     * @return array
     */
    public function &_get(): array {
        return $this->pointer;
    }

    /**
     * @param $offset
     * @param null $default
     * @return array|mixed
     */
    public function get($offset, $default = null) {
        return Arr::get($this->pointer, $offset, $default);
    }

    /**
     * @param $offset
     */
    public function _unset($offset) {
        Arr::unset($this->pointer, $offset);
    }

    /**
     * @param $offset
     * @return bool
     */
    public function _isset($offset): bool {
        return $this->get($offset, $id = unique_id(16)) !== $id;
    }

    /**
     * @return string
     */
    public function _toString() {
        return json_encode($this->pointer, JSON_PRETTY_PRINT);
    }

    public function output(){
        echo $this->_toString();
    }

    /**
     * @param $offset
     * @param $function
     * @param mixed ...$parameter
     * @note Due to the fact that the subject parameter position might vary across functions,
     * provision has been made for you to define the subject parameter with the key ":subject".
     * e.g to run a function like explode, you are to invoke it as follows: _call('offset', 'explode', 'delimiter', ':subject');
     * @return mixed|null
     */
    public function _call($offset, $function, ...$parameter) {
        if (!function_exists($function)) return $this->get($offset);
        if (!empty($parameter)) {
            $key = array_search(":subject", $parameter);
            if ($key !== false) $parameter[$key] = $this->get($offset);
        } else $parameter = [$this->get($offset)];
        return call_user_func($function, ...$parameter);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
        return $this->_isset($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
        return $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
        $this->set($offset, $value);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
        $this->_unset($offset);
    }

    /**
     * Count elements of an object
     * @link https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        // TODO: Implement count() method.
        return count($this->pointer);
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
        return json_encode($this->pointer);
    }

    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        // TODO: Implement getIterator() method.
        return new ArrayIterator($this->pointer);
    }

    /**
     * String representation of object
     * @link https://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        // TODO: Implement serialize() method.
        return serialize($this->pointer);
    }

    /**
     * Constructs the object
     * @link https://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        // TODO: Implement unserialize() method.
        $this->pointer = unserialize($serialized);
    }

    public function array_keys(): array
    {
        // TODO: Implement array_keys() method.
        return array_keys($this->pointer);
    }

    public function array_values(): array
    {
        // TODO: Implement array_values() method.
        return array_values($this->pointer);
    }

    public function key()
    {
        // TODO: Implement key() method.
        return key($this->pointer);
    }

    public function current()
    {
        // TODO: Implement current() method.
        return current($this->pointer);
    }

    public function shuffle(): void
    {
        // TODO: Implement shuffle() method.
        shuffle($this->pointer);
    }
}
