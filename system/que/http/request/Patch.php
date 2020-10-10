<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 3/17/2019
 * Time: 10:44 PM
 */

namespace que\http\request;


use ArrayIterator;
use que\support\Arr;
use que\support\interfaces\QueArrayAccess;
use Traversable;

class Patch implements QueArrayAccess
{
    /**
     * @var Patch
     */
    private static Patch $instance;

    /**
     * @var array
     */
    private array $pointer = [];

    /**
     * Patch constructor.
     */
    protected function __construct()
    {
        if (Request::getMethod() == Request::METHOD_PATCH) {
            $data = file_get_contents("php://input");
            parse_str($data, $this->pointer);
        }
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
     * @return Patch
     */
    public static function getInstance(): Patch
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
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
