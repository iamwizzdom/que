<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 3/17/2019
 * Time: 10:44 PM
 */

namespace que\session\type;

use ArrayIterator;
use JetBrains\PhpStorm\Pure;
use que\support\Arr;
use que\support\interfaces\QueArrayAccess;
use Traversable;

class Files implements QueArrayAccess
{
    /**
     * @var Files
     */
    private static Files $instance;

    /**
     * @var string
     */
    private string $session_id;

    /**
     * @var array
     */
    private array $pointer;

    protected function __construct(string $session_id)
    {
        $_SESSION[$this->session_id = $session_id] ??= [];
        $this->pointer = &$_SESSION[$session_id];
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
     * @param string $session_id
     * @return Files
     */
    public static function getInstance(string $session_id): Files
    {
        if (!isset(self::$instance))
            self::$instance = new self($session_id);
        return self::$instance;
    }

    /**
     * This method is used to switch the session or retrieve the current session id.
     * The session will be switched if a new session id is passed
     * with the $session_id param, while the current session id
     * will be returned if no session id is passed
     *
     * @param string|null $session_id
     * @return string
     */
    public function session_id(?string $session_id): string {
        if (empty($session_id)) return $this->session_id;
        if ($this->session_id == $session_id) return $this->session_id;
        $_SESSION[$this->session_id = $session_id] ??= [];
        $this->pointer = &$_SESSION[$session_id];
        return $this->session_id;
    }

    /**
     * This method is used to reset the current session id.
     *
     * @param string $session_id
     * @return string
     */
    public function reset_session_id(string $session_id): string {
        if ($this->session_id == $session_id) return $this->session_id;
        $_SESSION[$session_id] = $this->pointer;
        $this->pointer = &$_SESSION[$session_id];
        unset($_SESSION[$this->session_id]);
        return $this->session_id = $session_id;
    }

    /**
     * This method will destroy the current session
     */
    public function session_destroy(): void {
        $this->pointer = [];
        unset($_SESSION[$this->session_id]);
    }

    /**
     * @param $offset
     * @param $value
     * @return mixed
     */
    public function set($offset, $value): mixed
    {
        return Arr::set($this->pointer, $offset, $value);
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
    public function _unset($offset) {
        Arr::unset($this->pointer, $offset);
    }

    /**
     * @param $offset
     * @return bool
     */
    public function _isset($offset): bool {
        return $this->get($offset, $id = unique_id(5)) !== $id;
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
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        // TODO: Implement offsetExists() method.
        return isset($this->pointer[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        // TODO: Implement offsetGet() method.
        return $this->pointer[$offset] ?? null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet(mixed $offset, mixed $value)
    {
        // TODO: Implement offsetSet() method.
        if (is_null($offset)) $this->pointer[] = $value;
        else $this->pointer[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset(mixed $offset)
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
