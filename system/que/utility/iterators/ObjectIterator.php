<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/28/2019
 * Time: 10:39 PM
 */

namespace que\utility\iterators;


use ArrayAccess;

class ObjectIterator implements ArrayAccess
{

    /**
     * @var array
     */
    private $array;

    public function __construct(object $object)
    {
        $this->objectToArray($object);
    }

    /**
     * @return object
     */
    public function getObject(): object
    {
        return (object)$this->array;
    }

    /**
     * @param object $object
     */
    public function resetObject(object $object)
    {
        $this->objectToArray($object);
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
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
        return isset($this->array[$offset]);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
        return $this->array[$offset] ?? null;
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
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
        if (is_null($offset)) $this->array[] = $value;
        else $this->array[$offset] = $value;
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
        unset($this->array[$offset]);
    }

    /**
     * @param object $object
     */
    private function objectToArray(object $object) {
        foreach ($object as $key => $value) $this->array[$key] = $value;
    }
}