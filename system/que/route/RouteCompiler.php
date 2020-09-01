<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/27/2018
 * Time: 7:44 AM
 */

namespace que\route;

trait RouteCompiler
{

    /**
     * @return RouteRegistrar
     */
    public static function register() {
        return RouteRegistrar::register();
    }

    /**
     * @return RouteEntry[]
     */
    public static function &getRouteEntries(): array
    {
        return self::register()->getRouteEntries();
    }

    /**
     * Here we compile all registered routes by sorting them
     */
    protected static function compile() {

        self::sortRoutes(self::getRouteEntries());
    }

    /**
     * @param string $uri
     * @return array
     */
    public static function tokenizeUri(string $uri): array {

        $tokens = []; $args = [];

        if (preg_match_all("/{(.*?)}/", $uri, $matches)) {
            $args = array_map(function ($m) {
                return '{' . trim($m) . '}';
            }, $matches[1]);
        }

        foreach ($args as $key => $arg) {
            $val = sha1($arg);
            $args[$val] = $arg;
            $uri = str_replace($arg, $val, $uri);
            unset($args[$key]);
        }

        if (str_contains($uri, "#"))
            $uri = substr($uri, 0, strpos($uri, "#"));

        if (str_contains($uri, "?"))
            $uri = substr($uri, 0, strpos($uri, "?"));

        $uri_arr = array_filter(array_map('str_strip_spaces',
            explode("/", $uri)), function ($value) {
            return !empty($value);
        });

        foreach ($uri_arr as $token) {
            if (!empty($token) || $token == "0") {
                $tokens[] = $args[$token] ?? $token;
            }
        }

        return $tokens;
    }

    /**
     * @param array $routes
     */
    private static function sortRoutes(array &$routes) {

        $uriSizeList = []; $sortedRoutes = [];

        foreach ($routes as $k => $route) {
            if (!$route instanceof RouteEntry) continue;
            $route->uriTokens = self::tokenizeUri($route->getUri());
            $uriSizeList[$k] = array_size($route->uriTokens);
        }

        $sorted = bubble_sort($uriSizeList, true);

        foreach ($sorted as $value) {
            $key = array_search($value, $uriSizeList);
            $sortedRoutes[] = $routes[$key];
            unset($uriSizeList[$key]);
        }

        $routes = $sortedRoutes;
    }

}
