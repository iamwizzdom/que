<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/30/2019
 * Time: 7:35 PM
 */

namespace que\utility\pattern;


class ObjectPool
{
    /**
     * @var ObjectPool
     */
    private static $instance;

    /**
     * @var array
     */
    private $instances = [];

    protected function __construct()
    {
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
     * @return ObjectPool
     */
    public static function getInstance(): ObjectPool
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function load(string $key)
    {
        return $this->instances[$key] ?? null;
    }

    /**
     * @param string $key
     * @param object $object
     * @return $this
     */
    public function save(string $key, object $object): ObjectPool
    {
        $this->instances[$key] = $object;
        return $this;
    }
}
