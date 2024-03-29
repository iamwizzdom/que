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
use que\support\interfaces\QueArrayAccess;
use Traversable;

class Header implements QueArrayAccess
{
    protected const UPPER = '_ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    protected const LOWER = '-abcdefghijklmnopqrstuvwxyz';

    /**
     * @var Header
     */
    private static Header $instance;

    /**
     * @var array
     */
    private array $pointer;

    /**
     * Header constructor.
     */
    #[Pure] protected function __construct()
    {
        if (!function_exists('getallheaders')) {
            $this->pointer = [];
            return;
        }
        $header = getallheaders();
        $this->pointer = is_array($header) ? $header : [];
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
     * @return Header
     */
    public static function getInstance(): Header
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    public function setBulk(array $headers, bool $replace = true) {
        foreach ($headers as $key => $header) $this->set($key, $header, $replace);
    }

    /**
     * @param string $offset
     * @param string $data
     * @param bool $replace
     * @param int|null $response_code
     */
    public function set(string $offset, string $data, bool $replace = true, int $response_code = null): void {

        if ($this->_isset($offset) && !$replace) return;
        $this->pointer[$offset] = $data;
        header("{$offset}: {$data}", $replace, $response_code);
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
        return $this->pointer[$offset] ?? ($this->pointer[$this->translate($offset)] ?? $default);
    }

    /**
     * @param string $offset
     */
    public function _unset(string $offset) {
        unset($this->pointer[$offset]);
        unset($this->pointer[$this->translate($offset)]);
        header_remove($offset);
        header_remove($this->translate($offset));
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function _isset(string $offset): bool {
        return isset($this->pointer[$offset]) || isset($this->pointer[$this->translate($offset)]);
    }

    /**
     * @param $offset
     * @return bool
     */
    #[Pure] public function has($offset): bool
    {
        return array_key_exists($offset, $this->pointer) || array_key_exists($this->translate($offset), $this->pointer);
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
        return $this->has($offset);
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

    private function translate($key) {
        return strtr($key, self::UPPER, self::LOWER);
    }
}
