<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/22/2020
 * Time: 11:46 PM
 */

namespace que\support;


use que\config\Repository;

class Config
{

    /**
     * @param $files
     */
    public function load($files) {
        Repository::getInstance()->load($files, 'config');
    }

    /**
     * Get all the configuration items for the application.
     *
     * @return array
     */
    public static function all(): array
    {
        return Repository::getInstance()->get("config");
    }

    /**
     * Get the specified configuration value.
     *
     * @param $key
     * @param null $default
     * @return mixed
     */
    public static function get($key, $default = null): mixed
    {
        return Repository::getInstance()->get("config.{$key}", $default);
    }

    /**
     * Set a given configuration value.
     *
     * @param $key
     * @param null $value
     * @return Repository
     */
    public static function set($key, $value): Repository
    {
        return Repository::getInstance()->set("config.{$key}", $value);
    }

    /**
     * Unset a configuration option.
     *
     * @param $key
     */
    public static function unset($key)
    {
        Repository::getInstance()->remove("config.{$key}");
    }
}