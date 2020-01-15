<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 3/17/2019
 * Time: 10:44 PM
 */

namespace que\http\request;


use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Serializable;
use Traversable;

class Request implements ArrayAccess, Countable, JsonSerializable, IteratorAggregate, Serializable
{
    /**
     * @var Request
     */
    private static $instance;

    /**
     * @var array
     */
    private $pointer;

    /**
     * @var mixed
     */
    private $iterator = null;

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
     * @param string $offset
     * @param $data
     * @return $this
     */
    public function add(string $offset, $data): Request {
        $this->pointer[$offset] = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function &_get(): array {
        return $this->pointer;
    }

    /**
     * @param string $offset
     * @param null $default
     * @return mixed|null
     */
    public function get($offset, $default = null) {
        if (is_array($offset)) {

            $offset_value = current($offset);

            $key = key($offset);

            if (is_array($offset_value)) {

                if ($this->iterator === null && !isset($this->pointer[$key])) return $default;

                if ($this->iterator === null) {

                    $this->iterator = $this->pointer[$key];
                    $this->reset_pointer($this->iterator);
                    return $this->get($offset_value, $default);

                } else {

                    if (isset($this->iterator[$key])) {
                        $this->iterator = $this->iterator[$key];
                        $this->reset_pointer($this->iterator);
                    }

                    return $this->get($offset_value, $default);
                }


            } else {

                if ($this->iterator === null) {

                    if (isset($this->pointer[$key])) {
                        $this->iterator = $this->pointer[$key];
                        $this->reset_pointer($this->iterator);
                    }

                    return $this->get($offset_value, $default);
                }

                if (isset($this->iterator[$key])) $this->iterator = $this->iterator[$key];
                $this->reset_pointer($this->iterator);
                return $this->get($offset_value, $default);
            }

        } else {
            $value = isset($this->pointer[$offset]) ? $this->pointer[$offset] : $default;
            $this->reset_pointer($_REQUEST);
            return $value;
        }
    }

    /**
     * @param $pointer
     */
    private function reset_pointer(&$pointer) {
        $this->pointer = &$pointer;
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function has(string $offset): bool {
        return $this->get($offset, false) !== false;
    }

    /**
     * @param string $offset
     */
    public function _unset(string $offset) {
        unset($this->pointer[$offset]);
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
        if (is_null($offset)) $this->pointer[] = $value;
        else $this->pointer[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
        unset($this->pointer[$offset]);
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
        return $this->pointer;
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
        return $this->pointer = unserialize($serialized);
    }

}