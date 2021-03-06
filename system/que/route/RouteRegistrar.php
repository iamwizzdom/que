<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 3/16/2019
 * Time: 8:32 PM
 */

namespace que\route;

use Closure;
use que\common\exception\RouteException;
use que\error\RuntimeError;

class RouteRegistrar
{

    /**
     * @var RouteRegistrar
     */
    private static RouteRegistrar $instance;

    /**
     * @var RouteEntry[]
     */
    private array $queue = [];

    /**
     * RouteRegistrar constructor.
     */
    protected function __construct()
    {
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * @return RouteRegistrar
     */
    protected static function getInstance(): RouteRegistrar
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param string $prefix
     * @param Closure $callback
     * @param Closure|null $entryCallback
     * @return $this
     */
    public function group(string $prefix, Closure $callback, Closure $entryCallback = null): RouteRegistrar {

        try {
            $group_arr = call_user_func($callback, $prefix);

            if (!is_array($group_arr))
                throw new RouteException("Route group callback must return an array", "Route Error");

            foreach ($group_arr as $key => $callback) {


                if (!$callback instanceof Closure || !is_callable($callback))
                    throw new RouteException("All elements in route group list must be callable", "Route Error");

                $entry = new RouteEntry();

                if ($entryCallback) call_user_func($entryCallback, $entry);
                call_user_func($callback, $entry);


                $entry->setType(strtolower($entry->getType()));

                if ('web' !== $entry->getType() && 'api' !== $entry->getType() && 'resource' !== $entry->getType())
                    throw new RouteException("Invalid group route type for {$prefix}[::]{$entry->getUri()}", "Route Error");

                $route = trim($entry->getUri());

                if (!empty($prefix)) {
                    $prefix = trim($prefix);
                    $route = "{$prefix}/{$route}";
                }

                $route = str_strip_repeated_char('\/', $route);
                $route = str_strip_repeated_char('\\\\', $route);

                $entry->setUri($route);

                array_push($this->queue, $entry);
            }

        } catch (RouteException $e) {

            RuntimeError::render(E_USER_ERROR, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(), $e->getTitle());
        }

        return $this;
    }

    /**
     * @param string $prefix
     * @param Closure $callback
     * @param Closure|null $entryCallback
     * @return $this
     */
    public function groupWeb(string $prefix, Closure $callback, Closure $entryCallback = null): RouteRegistrar {

        try {
            $group_arr = call_user_func($callback, $prefix);

            if (!is_array($group_arr))
                throw new RouteException("Route group callback must return an array", "Route Error");

            foreach ($group_arr as $key => $callback) {

                if (!$callback instanceof Closure || !is_callable($callback))
                    throw new RouteException("All elements in route group list must be callable", "Route Error");

                $entry = new RouteEntry();
                $entry->setContentTypeByExtension('html');

                if ($entryCallback) call_user_func($entryCallback, $entry);
                call_user_func($callback, $entry);

                $entry->setType('web');

                $route = trim($entry->getUri());

                if (!empty($prefix)) {
                    $prefix = trim($prefix);
                    $route = "{$prefix}/{$route}";
                }

                $route = str_strip_repeated_char('\/', $route);
                $route = str_strip_repeated_char('\\\\', $route);

                $entry->setUri($route);

                array_push($this->queue, $entry);
            }

        } catch (RouteException $e) {

            RuntimeError::render(E_USER_ERROR, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(), $e->getTitle());
        }

        return $this;
    }

    /**
     * @param string $prefix
     * @param Closure $callback
     * @param Closure|null $entryCallback
     * @return $this
     */
    public function groupApi(string $prefix, Closure $callback, Closure $entryCallback = null): RouteRegistrar {

        try {
            $group_arr = call_user_func($callback, $prefix);

            if (!is_array($group_arr))
                throw new RouteException("Route group callback must return an array", "Route Error");

            foreach ($group_arr as $key => $callback) {

                if (!$callback instanceof Closure || !is_callable($callback))
                    throw new RouteException("All elements in route group list must be callable", "Route Error");

                $entry = new RouteEntry();
                $entry->setContentTypeByExtension('json');

                if ($entryCallback) call_user_func($entryCallback, $entry);
                call_user_func($callback, $entry);

                $entry->setType('api');

                $route = trim($entry->getUri());

                if (!empty($prefix)) {
                    $prefix = trim($prefix);
                    $route = "{$prefix}/{$route}";
                }

                $route = str_strip_repeated_char('\/', $route);
                $route = str_strip_repeated_char('\\\\', $route);

                $entry->setUri($route);

                array_push($this->queue, $entry);
            }

        } catch (RouteException $e) {

            RuntimeError::render(E_USER_ERROR, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(), $e->getTitle());
        }

        return $this;
    }

    /**
     * @param string $prefix
     * @param Closure $callback
     * @param Closure|null $entryCallback
     * @return $this
     */
    public function groupResource(string $prefix, Closure $callback, Closure $entryCallback = null): RouteRegistrar {

        try {
            $group_arr = call_user_func($callback, $prefix);

            if (!is_array($group_arr))
                throw new RouteException("Route group callback must return an array", "Route Error");

            foreach ($group_arr as $key => $callback) {

                if (!$callback instanceof Closure || !is_callable($callback))
                    throw new RouteException("All elements in route group list must be callable", "Route Error");

                $entry = new RouteEntry();

                if ($entryCallback) call_user_func($entryCallback, $entry);
                call_user_func($callback, $entry);

                $entry->setType('resource');

                $route = trim($entry->getUri());

                if (!empty($prefix)) {
                    $prefix = trim($prefix);
                    $route = "{$prefix}/{$route}";
                }

                $route = str_strip_repeated_char('\/', $route);
                $route = str_strip_repeated_char('\\\\', $route);

                $entry->setUri($route);

                array_push($this->queue, $entry);
            }

        } catch (RouteException $e) {

            RuntimeError::render(E_USER_ERROR, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(), $e->getTitle());
        }

        return $this;
    }

    /**
     * @param Closure $callback
     * @return RouteRegistrar
     */
    public function web(Closure $callback): RouteRegistrar {

        $entry = new RouteEntry();
        $entry->setContentTypeByExtension('html');

        call_user_func($callback, $entry);

        $route = $entry->getUri();

        $route = str_strip_repeated_char('\/', $route);
        $route = str_strip_repeated_char('\\\\', $route);

        $entry->setUri($route);

        $entry->setType('web');

        array_push($this->queue, $entry);

        return $this;
    }

    /**
     * @param Closure $callback
     * @return RouteRegistrar
     */
    public function api(Closure $callback): RouteRegistrar {

        $entry = new RouteEntry();
        $entry->setContentTypeByExtension('json');

        call_user_func($callback, $entry);

        $route = $entry->getUri();

        $route = str_strip_repeated_char('\/', $route);
        $route = str_strip_repeated_char('\\\\', $route);

        $entry->setUri($route);

        $entry->setType('api');

        array_push($this->queue, $entry);

        return $this;
    }

    /**
     * @param Closure $callback
     * @return RouteRegistrar
     */
    public function resource(Closure $callback): RouteRegistrar {

        $entry = new RouteEntry();

        call_user_func($callback, $entry);

        $route = $entry->getUri();

        $route = str_strip_repeated_char('\/', $route);
        $route = str_strip_repeated_char('\\\\', $route);

        $entry->setUri($route);

        $entry->setType('resource');

        array_push($this->queue, $entry);

        return $this;
    }

    /**
     * @return RouteEntry[]
     */
    public function &getRouteEntries(): array {
        return $this->queue;
    }

    /**
     * @return RouteRegistrar
     */
    public static function register() {
        return self::getInstance();
    }

}
