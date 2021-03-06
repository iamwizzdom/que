<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 3/17/2019
 * Time: 10:44 PM
 */

namespace que\http\input;

use ArrayIterator;
use JetBrains\PhpStorm\Pure;
use que\common\validator\condition\Condition;
use que\http\HTTP;
use que\http\request\Cookie;
use que\http\request\Delete;
use que\http\request\Files;
use que\http\request\Get;
use que\http\request\Header;
use que\http\request\Patch;
use que\http\request\Post;
use que\http\request\Put;
use que\http\request\Request;
use que\http\request\Server;
use que\support\Arr;
use que\support\interfaces\QueArrayAccess;
use que\user\User;
use Traversable;

class Input implements QueArrayAccess
{
    /**
     * @var Input
     */
    private static Input $instance;

    /**
     * @var array
     */
    private array $pointer;

    /**
     * @var Cookie
     */
    private Cookie $cookie;

    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var Post
     */
    private Post $post;

    /**
     * @var Put
     */
    private Put $put;

    /**
     * @var Patch
     */
    private Patch $patch;

    /**
     * @var Delete
     */
    private Delete $delete;

    /**
     * @var Get
     */
    private Get $get;

    /**
     * @var Server
     */
    private Server $server;

    /**
     * @var Files
     */
    private Files $files;

    /**
     * @var Header
     */
    private Header $header;

    /**
     * Input constructor.
     */
    protected function __construct()
    {
        $this->cookie = HTTP::getInstance()->_cookie();
        $this->request = HTTP::getInstance()->_request();
        $this->pointer = $this->get_all_input();
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
     * @return Input
     */
    public static function getInstance(): Input
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param string $offset
     * @return Condition
     */
    public function validate(string $offset): Condition
    {
        return new Condition($offset, $this->get($offset));
    }

    /**
     * @return Post
     */
    public function getPost(): Post
    {
        return $this->post;
    }

    /**
     * @return Put
     */
    public function getPut(): Put
    {
        return $this->put;
    }

    /**
     * @return Patch
     */
    public function getPatch(): Patch
    {
        return $this->patch;
    }

    /**
     * @return Delete
     */
    public function getDelete(): Delete
    {
        return $this->delete;
    }

    /**
     * @return Get
     */
    public function getGet(): Get
    {
        return $this->get;
    }

    /**
     * @return Files
     */
    public function getFiles(): Files
    {
        return $this->files;
    }

    /**
     * @return Server
     */
    public function getServer(): Server
    {
        return $this->server;
    }

    /**
     * @return Header
     */
    public function getHeader(): Header
    {
        return $this->header;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return Cookie
     */
    public function getCookie(): Cookie
    {
        return $this->cookie;
    }

    /**
     * @return string|null
     */
    public function getBody(): ?string
    {
        return (file_get_contents("php://input") ?: null);
    }

    /**
     * @return array
     */
    private function get_all_input(): array {

        $body = $this->getBody(); $bodyArr = [];
        $this->header = HTTP::getInstance()->_header();
        if (!empty($body) && $this->header->has('Content-Type')) {
            $contentType = $this->header->get('Content-Type');
            $mimeType = extension_from_mime_type($contentType);
            if ('json' === $mimeType) $bodyArr = json_decode($body, true) ?: [];
        }
        return array_merge(
            ($this->post = HTTP::getInstance()->_post())->_get(),
            ($this->put = HTTP::getInstance()->_put())->_get(),
            ($this->patch = HTTP::getInstance()->_patch())->_get(),
            ($this->delete = HTTP::getInstance()->_delete())->_get(),
            ($this->get = HTTP::getInstance()->_get())->_get(),
            ($this->server = HTTP::getInstance()->_server())->_get(),
            $this->header->_get(),
            ($this->files = HTTP::getInstance()->_files())->_get(),
            $bodyArr
        );
    }

    /**
     * @return User|null
     */
    public function user(): ?User {
        return User::isLoggedIn() ? User::getInstance() : null;
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
     * @return mixed
     */
    public function get($offset, $default = null): mixed
    {
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
    public function _toString(): string
    {
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
     * @return mixed|null
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
