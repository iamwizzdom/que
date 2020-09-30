<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 3/17/2019
 * Time: 10:44 PM
 */

namespace que\http\request;


use ArrayIterator;
use que\support\interfaces\QueArrayAccess;
use Traversable;

class Header implements QueArrayAccess
{
    /**
     * @var Header
     */
    private static Header $instance;

    /**
     * @var array|false
     */
    private $pointer;

    /**
     * Header constructor.
     */
    protected function __construct()
    {
        $this->pointer = getallheaders();
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
     * @param int $response_code
     */
    public function set(string $offset, string $data, bool $replace = true, int $response_code = null): void {
        if ($this->_isset($offset) && !$replace) return;
        $this->pointer[$offset] = $data;
        header("{$offset}: {$data}", $replace, $response_code);
    }

    /**
     * @return array|false
     */
    public function &_get() {
        return $this->pointer;
    }

    /**
     * @param $offset
     * @param null $default
     * @return mixed|null
     */
    public function get($offset, $default = null) {
        return $this->pointer[$offset] ?? $default;
    }

    /**
     * @param string $offset
     */
    public function _unset(string $offset) {
        unset($this->pointer[$offset]);
        header_remove($offset);
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function _isset(string $offset): bool {
        return isset($this->pointer[$offset]);
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
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
        return isset($this->pointer[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
        return $this->pointer[$offset] ?? null;
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
        unset($this->pointer[$offset]);
        header_remove($offset);
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
