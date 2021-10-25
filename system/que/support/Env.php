<?php

namespace que\support;

use que\config\Repository;

class Env
{
    /**
     * Path to the .env file.
     * @var string
     */
    protected string $path;

    /**
     * @var Repository
     */
    private Repository $repository;


    public function __construct(string $path)
    {
        if(!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('%s does not exist', $path));
        }
        $this->path = $path;
        $this->repository = Repository::getInstance();
    }

    public function load() :void
    {
        if (!is_readable($this->path)) {
            throw new \RuntimeException(sprintf('%s file is not readable', $this->path));
        }

        $env = [];
        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {

            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);

            $value = match (strtolower(trim($value))) {
                'true', '(true)' => true,
                'false', '(false)' => false,
                'empty', '(empty)' => '',
                'null', '(null)' => null,
                default => trim($value),
            };

            if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
                $value = $matches[2];
            }

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
            $env[$name] = $value;
        }
        $this->repository->set('env', $env);
    }

    /**
     * Get all the configuration items for the application.
     *
     * @return array
     */
    public static function all(): array
    {
        return Repository::getInstance()->get("env");
    }

    /**
     * Get the specified environment value.
     *
     * @param $key
     * @param null $default
     * @return mixed
     */
    public static function get($key, $default = null): mixed
    {
        return Repository::getInstance()->get("env.{$key}", $default);
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
        return Repository::getInstance()->set("env.{$key}", $value);
    }

    /**
     * Unset a configuration option.
     *
     * @param $key
     */
    public static function unset($key)
    {
        Repository::getInstance()->remove("env.{$key}");
    }
}