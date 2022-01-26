<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 3/17/2019
 * Time: 10:44 PM
 */

namespace que\http\request;


use ArrayIterator;
use JetBrains\PhpStorm\Pure;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\http\HTTP;
use que\support\Arr;
use que\support\interfaces\QueArrayAccess;
use que\support\Str;
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
    const METHOD_OPTIONS = 'OPTIONS';

    /**
     * @var bool
     */
    protected static bool $httpMethodOverride = false;

    /**
     * @var string|null
     */
    protected ?string $baseUrl = null;

    /**
     * @var string|null
     */
    protected ?string $requestUri = null;

    /**
     * @var string|null
     */
    protected ?string $pathInfo = null;

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
    public static function getHttpMethodOverrideStatus(): bool
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
     * but only if @return string|null The request method
     *
     * @see enableHttpMethodOverride() has been called.
     *
     * The method is always an uppercased string.
     *
     * @see getRealMethod()
     */
    public static function getMethod(): ?string
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
    public static function getRealMethod(): string
    {
        return strtoupper(server('REQUEST_METHOD', 'GET'));
    }

    /**
     * Checks whether or not the method is supported by Que
     *
     * @param string|null $method
     * @return bool
     */
    public static function isSupportedMethod(string $method = null): bool
    {
        return in_array(($method !== null ? strtoupper($method) : self::getMethod()), self::getSupportedMethods(), true);
    }

    /**
     * @return array
     */
    private static function getSupportedMethods(): array
    {
        if (!empty(self::$supportedMethods)) return self::$supportedMethods;
        $const = (new ReflectionClass(self::class))->getConstants();
        return self::$supportedMethods = array_filter($const, function ($key) {
            return str__starts_with($key, "METHOD");
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Returns the clients real IP address
     *
     * @return mixed
     */
    public static function getClientIp(): mixed
    {
        return IP::real();
    }

    /**
     * Returns the user.
     *
     * @return string|null
     */
    public static function getAuthUser(): ?string
    {
        return headers('PHP_AUTH_USER');
    }

    /**
     * Returns the password.
     *
     * @return string|null
     */
    public static function getAuthPassword(): ?string
    {
        return headers('PHP_AUTH_PW');
    }

    /**
     * Returns the protocol version.
     *
     * @return string|null
     */
    public static function getProtocolVersion(): ?string
    {
        return server('SERVER_PROTOCOL');
    }

    /**
     * Returns true if the request is an XMLHttpRequest.
     *
     * It works if your JavaScript library sets an X-Requested-With HTTP header.
     * It is known to work with common JavaScript frameworks:
     *
     * @see https://wikipedia.org/wiki/List_of_Ajax_frameworks#JavaScript
     *
     * @return bool true if the request is an XMLHttpRequest, false otherwise
     */
    public function isXmlHttpRequest()
    {
        return 'XMLHttpRequest' == headers('X-Requested-With');
    }

    /**
     * Normalizes a query string.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized,
     * have consistent escaping and unneeded delimiters are removed.
     *
     * @return string A normalized query string for the Request
     */
    public static function normalizeQueryString(?string $qs)
    {
        if ('' === ($qs ?? '')) {
            return '';
        }

        $qs = Str::parse_query($qs);
        ksort($qs);

        return http_build_query($qs, '', '&', \PHP_QUERY_RFC3986);
    }

    /**
     * Generates the normalized query string for the Request.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized
     * and have consistent escaping.
     *
     * @return string|null A normalized query string for the Request
     */
    public function getQueryString()
    {
        $qs = static::normalizeQueryString(server('QUERY_STRING'));

        return '' === $qs ? null : $qs;
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
    public static function isSecure(): bool
    {
        $proto = server("HTTP_X_FORWARDED_PROTO");
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
    public static function getHost(): string
    {
        $host = server('HTTP_X_FORWARDED_HOST');

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
    public static function getPort(): int|string
    {

        if ($host = headers('X-Forwarded-Port')) {
            goto PROCEED;
        } elseif ($host = headers('X-Forwarded-Host')) {
            goto PROCEED;
        } elseif (!$host = headers('HOST')) {
            return server('SERVER_PORT', 80);
        }

        PROCEED:

        if ('[' === $host[0]) {
            $pos = strpos($host, ':', strrpos($host, ']'));
        } else {
            $pos = strrpos($host, ':');
        }

        if (false !== $pos && $port = substr($host, $pos + 1)) {
            return (int)$port;
        }

        return 'https' === self::getScheme() ? 443 : 80;
    }

    /**
     * Gets the request's scheme.
     *
     * @return string
     */
    public static function getScheme(): string
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
    public static function getHttpHost(): string
    {
        $scheme = self::getScheme();
        $port = self::getPort();

        if (('http' == $scheme && 80 == $port) || ('https' == $scheme && 443 == $port)) {
            return self::getHost();
        }

        return self::getHost() . ":{$port}";
    }

    /**
     * Get the request origin
     * @return mixed
     */
    public static function getOrigin() {
        return headers('Origin');
    }

    /**
     * Returns the requested URI (path and query string).
     *
     * @return string The raw URI (i.e. not URI decoded)
     */
    public function getRequestUri()
    {
        if (null === $this->requestUri) {
            $this->requestUri = $this->prepareRequestUri();
        }

        return $this->requestUri;
    }

    /**
     * Gets the scheme and HTTP host.
     *
     * If the URL was called with basic authentication, the user
     * and the password are not added to the generated string.
     *
     * @return string The scheme and HTTP host
     */
    public function getSchemeAndHttpHost(): string
    {
        return $this->getScheme() . '://' . $this->getHttpHost();
    }

    /**
     * Returns the prefix as encoded in the string when the string starts with
     * the given prefix, null otherwise.
     */
    private function getUrlencodedPrefix(string $string, string $prefix): ?string
    {
        if (0 !== strpos(rawurldecode($string), $prefix)) {
            return null;
        }

        $len = \strlen($prefix);

        if (preg_match(sprintf('#^(%%[[:xdigit:]]{2}|.){%d}#', $len), $string, $match)) {
            return $match[0];
        }

        return null;
    }

    /**
     * Prepares the path info.
     *
     * @return string path info
     */
    protected function preparePathInfo()
    {
        if (null === ($requestUri = Request::getUri())) {
            return '/';
        }

        // Remove the query string from REQUEST_URI
        if (false !== $pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }
        if ('' !== $requestUri && '/' !== $requestUri[0]) {
            $requestUri = '/' . $requestUri;
        }

        if (null === ($baseUrl = $this->getBaseUrlReal())) {
            return $requestUri;
        }

        $pathInfo = str_start_from($requestUri, $baseUrl);
        if (false === $pathInfo || '' === $pathInfo) {
            // If substr() returns false then PATH_INFO is set to an empty string
            return '/';
        }

        return (string)$pathInfo;
    }

    /**
     * Returns the path being requested relative to the executed script.
     *
     * The path info always starts with a /.
     *
     * Suppose this request is instantiated from /mysite on localhost:
     *
     *  * http://localhost/mysite              returns an empty string
     *  * http://localhost/mysite/about        returns '/about'
     *  * http://localhost/mysite/enco%20ded   returns '/enco%20ded'
     *  * http://localhost/mysite/about?var=1  returns '/about'
     *
     * @return string The raw path (i.e. not urldecoded)
     */
    public function getPathInfo()
    {
        if (null === $this->pathInfo) {
            $this->pathInfo = $this->preparePathInfo();
        }

        return $this->pathInfo;
    }

    /**
     * Get the current path info for the request.
     *
     * @return string
     */
    public function path()
    {
        $pattern = trim($this->getPathInfo(), '/');

        return $pattern === '' ? '/' : $pattern;
    }

    /**
     * Get the current decoded path info for the request.
     *
     * @return string
     */
    public function decodedPath()
    {
        return rawurldecode($this->path());
    }

    /*
     * The following methods are derived from code of the Zend Framework (1.10dev - 2010-01-24)
     *
     * Code subject to the new BSD license (https://framework.zend.com/license).
     *
     * Copyright (c) 2005-2010 Zend Technologies USA Inc. (https://www.zend.com/)
     */

    protected function prepareRequestUri()
    {
        $requestUri = '';

        if ('1' == server('IIS_WasUrlRewritten') && '' != server('UNENCODED_URL')) {
            // IIS7 with URL Rewrite: make sure we get the unencoded URL (double slash problem)
            $requestUri = server('UNENCODED_URL');
            \http()->_server()->_unset('UNENCODED_URL');
            \http()->_server()->_unset('IIS_WasUrlRewritten');
        } elseif (\http()->_server()->_isset('REQUEST_URI')) {
            $requestUri = server('REQUEST_URI');

            if ('' !== $requestUri && '/' === $requestUri[0]) {
                // To only use path and query remove the fragment.
                if (false !== $pos = strpos($requestUri, '#')) {
                    $requestUri = substr($requestUri, 0, $pos);
                }
            } else {
                // HTTP proxy reqs setup request URI with scheme and host [and port] + the URL path,
                // only use URL path.
                $uriComponents = parse_url($requestUri);

                if (isset($uriComponents['path'])) {
                    $requestUri = $uriComponents['path'];
                }

                if (isset($uriComponents['query'])) {
                    $requestUri .= '?'.$uriComponents['query'];
                }
            }
        } elseif (\http()->_server()->_isset('ORIG_PATH_INFO')) {
            // IIS 5.0, PHP as CGI
            $requestUri = server('ORIG_PATH_INFO');
            if ('' != server('QUERY_STRING')) {
                $requestUri .= '?'.server('QUERY_STRING');
            }
            \http()->_server()->_unset('ORIG_PATH_INFO');
        }

        // normalize the request URI to ease creating sub-requests from this request
        \http()->_server()->set('REQUEST_URI', $requestUri);

        return $requestUri;
    }

    /**
     * Prepares the base URL.
     *
     * @return string
     */
    protected function prepareBaseUrl()
    {
        $filename = basename(server('SCRIPT_FILENAME'));

        if (basename(server('SCRIPT_NAME')) === $filename) {
            $baseUrl = server('SCRIPT_NAME');
        } elseif (basename(server('PHP_SELF')) === $filename) {
            $baseUrl = server('PHP_SELF');
        } elseif (basename(server('ORIG_SCRIPT_NAME')) === $filename) {
            $baseUrl = server('ORIG_SCRIPT_NAME'); // 1and1 shared hosting compatibility
        } else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path = server('PHP_SELF', '');
            $file = server('SCRIPT_FILENAME', '');
            $segs = explode('/', trim($file, '/'));
            $segs = array_reverse($segs);
            $index = 0;
            $last = \count($segs);
            $baseUrl = '';
            do {
                $seg = $segs[$index];
                $baseUrl = '/'.$seg.$baseUrl;
                ++$index;
            } while ($last > $index && (false !== $pos = strpos($path, $baseUrl)) && 0 != $pos);
        }

        // Does the baseUrl have anything in common with the request_uri?
        $requestUri = $this->getRequestUri();
        if ('' !== $requestUri && '/' !== $requestUri[0]) {
            $requestUri = '/'.$requestUri;
        }

        if ($baseUrl && null !== $prefix = $this->getUrlencodedPrefix($requestUri, $baseUrl)) {
            // full $baseUrl matches
            return $prefix;
        }

        if ($baseUrl && null !== $prefix = $this->getUrlencodedPrefix($requestUri, rtrim(\dirname($baseUrl), '/'.\DIRECTORY_SEPARATOR).'/')) {
            // directory portion of $baseUrl matches
            return rtrim($prefix, '/'.\DIRECTORY_SEPARATOR);
        }

        $truncatedRequestUri = $requestUri;
        if (false !== $pos = strpos($requestUri, '?')) {
            $truncatedRequestUri = substr($requestUri, 0, $pos);
        }

        $basename = basename($baseUrl);
        if (empty($basename) || !strpos(rawurldecode($truncatedRequestUri), $basename)) {
            // no match whatsoever; set it blank
            return '';
        }

        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of baseUrl. $pos !== 0 makes sure it is not matching a value
        // from PATH_INFO or QUERY_STRING
        if (\strlen($requestUri) >= \strlen($baseUrl) && (false !== $pos = strpos($requestUri, $baseUrl)) && 0 !== $pos) {
            $baseUrl = substr($requestUri, 0, $pos + \strlen($baseUrl));
        }

        return rtrim($baseUrl, '/'.\DIRECTORY_SEPARATOR);
    }

    /**
     * @return string|null
     * @throws \que\common\exception\RouteException
     */
    public function getBaseUrl()
    {
        if ($this->baseUrl === null) $this->baseUrl = base_url();
        return $this->baseUrl;
    }

    /**
     * Returns the real base URL received by the webserver from which this request is executed.
     * The URL does not include trusted reverse proxy prefix.
     *
     * @return string The raw URL (i.e. not urldecoded)
     */
    private function getBaseUrlReal()
    {
        if (null === $this->baseUrl) {
            $this->baseUrl = $this->prepareBaseUrl();
        }

        return $this->baseUrl;
    }

    /**
     * @return string
     */
    public function url(): string
    {
        return current_url();
    }

    public function fullUrl()
    {
        $query = $this->getQueryString();

        $question = ($this->getBaseUrl() . ($this->getPathInfo() === '/' ? '/?' : '?'));

        return $query ? ($this->url() . $question . $query) : $this->url();
    }

    /**
     * Determine if the current request URL and query string match a pattern.
     *
     * @param mixed ...$patterns
     * @return bool
     */
    public function fullUrlIs(...$patterns)
    {
        $url = $this->fullUrl();

        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $url)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the current request URI matches a pattern.
     *
     * @param  mixed  ...$patterns
     * @return bool
     */
    public function is(...$patterns)
    {
        $path = $this->decodedPath();

        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the request URI
     *
     * This method return the URI from your project root,
     * ignoring any uri before the root.
     *
     * To get the full URI at all times
     * @return mixed
     * @see getUriOriginal()
     *
     */
    public static function getUri(): mixed
    {
        return server('REQUEST_URI');
    }

    /**
     * Returns the full URI at all times
     *
     * @return mixed
     */
    public static function getUriOriginal(): mixed
    {
        return server('REQUEST_URI_ORIGINAL');
    }

    /**
     * Returns all defined URI params if any
     * @return array|null
     */
    public static function getUriParams(): ?array
    {
        return server('route.params');
    }

    /**
     * Returns a defined URI param
     * @param string $key
     * @return mixed
     */
    public static function getUriParam(string $key): mixed
    {
        return server("route.params.{$key}");
    }

    /**
     * @param $offset
     * @param $value
     */
    public function set($offset, $value)
    {
        Arr::set($this->pointer, $offset, $value);
    }

    /**
     * @return array
     */
    public function &_get(): array
    {
        return $this->pointer;
    }

    /**
     * @param $offset
     * @param null $default
     * @return mixed
     */
    public function get($offset, $default = null): mixed
    {
        return Arr::get($this->pointer, $offset, $default);
    }

    /**
     * @param $offset
     */
    public function _unset($offset)
    {
        Arr::unset($this->pointer, $offset);
    }

    /**
     * @param $offset
     * @return bool
     */
    public function _isset($offset): bool
    {
        return $this->get($offset, $id = unique_id(16)) !== $id;
    }

    /**
     * @return string
     */
    public function _toString(): string
    {
        return json_encode($this->pointer, JSON_PRETTY_PRINT);
    }

    public function output()
    {
        echo $this->_toString();
    }

    /**
     * @param $offset
     * @param $function
     * @param mixed ...$parameter
     * @return mixed
     * @note Due to the fact that the subject parameter position might vary across functions,
     * provision has been made for you to define the subject parameter with the key ":subject".
     * e.g to run a function like explode, you are to invoke it as follows: _call('offset', 'explode', 'delimiter', ':subject');
     */
    public function _call($offset, $function, ...$parameter): mixed
    {
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
    public function offsetExists(mixed $offset): bool
    {
        // TODO: Implement offsetExists() method.
        return $this->_isset($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        // TODO: Implement offsetGet() method.
        return $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet(mixed $offset, mixed $value)
    {
        // TODO: Implement offsetSet() method.
        $this->set($offset, $value);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset(mixed $offset)
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
    #[Pure] public function count(): int
    {
        // TODO: Implement count() method.
        return count($this->pointer);
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array
    {
        // TODO: Implement jsonSerialize() method.
        return $this->pointer;
    }

    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable|ArrayIterator An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator(): Traversable|ArrayIterator
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
    public function serialize(): string
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

    #[Pure] public function array_keys(): array
    {
        // TODO: Implement array_keys() method.
        return array_keys($this->pointer);
    }

    #[Pure] public function array_values(): array
    {
        // TODO: Implement array_values() method.
        return array_values($this->pointer);
    }

    #[Pure] public function key(): int|string|null
    {
        // TODO: Implement key() method.
        return key($this->pointer);
    }

    #[Pure] public function current(): mixed
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
