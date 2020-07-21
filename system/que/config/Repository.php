<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/20/2020
 * Time: 7:02 PM
 */

namespace que\config;


use ArrayAccess;
use que\support\Arr;

class Repository implements ArrayAccess
{
    /**
     * @var Repository
     */
    private static Repository $instance;

    /**
     * @var array
     */
    private array $repository = [];

    /**
     * Repository constructor.
     */
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
     * @return Repository
     */
    public static function getInstance(): Repository
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * Determine whether the config array contains the given key
     *
     * @param string $offset
     * @return bool
     */
    public function has($offset)
    {
        return Arr::has($this->repository, $offset);
    }

    /**
     * Set a value on the config array
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            Arr::set($this->repository, $key, $value);
        }
        return $this;
    }

    /**
     * Get an item from the config array
     *
     * If the key does not exist the default
     * value should be returned
     *
     * @param $offset
     * @param null $default
     * @return mixed|null
     */
    public function get($offset, $default = null)
    {
        return Arr::get($this->repository, $offset, $default);
    }

    /**
     * Remove an item from the config array
     *
     * @param $offset
     * @return $this
     */
    public function remove($offset)
    {
        return $this->set($offset, null);
    }

    /**
     * Get all of the configuration items for the application.
     *
     * @return array
     */
    public function all()
    {
        return $this->repository;
    }

    /**
     * Load config items from a file or an array of files
     *
     * The file name should be the config key and the value
     * should be the return value from the file
     *
     * @param array|string $files : The full path to the files
     * @param null $offset
     */
    public function load($files, $offset = null)
    {
        if (is_array($files)) {

            foreach ($files as $file) {
                $this->addFileToRepo($file, $offset);
            }

        } else $this->addFileToRepo($files, $offset);
    }

    /**
     * @param $file
     * @param $offset
     */
    private function addFileToRepo($file, $offset) {
        if (file_exists($file)) {
            $fileName = pathinfo($file, PATHINFO_FILENAME);
            $fileData = include "$file";
            if (!is_blank($offset = value($offset))) {
                $this->repository[$offset][$fileName] = $fileData;
            } else $this->repository[$fileName] = $fileData;
        }
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
        return $this->has($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
        return $this->get($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
        $this->set($offset, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
        $this->remove($offset);
    }
}