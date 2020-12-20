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

        if (str__contains($uri, "#"))
            $uri = substr($uri, 0, strpos($uri, "#"));

        if (str__contains($uri, "?"))
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

        $sorted_sizes = bubble_sort($uriSizeList);

        foreach ($sorted_sizes as $value) {
            $key = array_search($value, $uriSizeList);
            $sortedRoutes[] = $routes[$key];
            unset($uriSizeList[$key]);
        }

        $routes = self::sortRoutesByArg($sortedRoutes, array_unique($sorted_sizes));
    }

    private static function sortRoutesByArg(array $routes, array $sizes) {

        $group = [];

        foreach ($sizes as $size) {

            $group[$size] = array_filter($routes, function ($route) use ($size){
                return array_size($route->uriTokens) == $size;
            });

            $group_without_arg = []; $group_with_arg = []; $arg_sizes = [];

            foreach ($group[$size] as $key => $route) {
                if (!$route instanceof RouteEntry) continue;
                if (RouteInspector::routeHasArgs($route)) {
                    $arg_size = 0;
                    foreach ($route->uriTokens as $arg) if (preg_match("/{(.*?)}/", $arg) == 1) $arg_size++;
                    $arg_sizes[$key] = $arg_size;
                    $group_with_arg[$key] = $route;
                } else $group_without_arg[] = $route;
            }

            if (!empty($group_with_arg)) {
                $sorted_sizes = bubble_sort($arg_sizes);
                foreach ($sorted_sizes as $sorted_size) {
                    $key = array_search($sorted_size, $arg_sizes);
                    $group_without_arg[] = $group_with_arg[$key];
                    unset($arg_sizes[$key]);
                }
            }

            $group[$size] = $group_without_arg;
        }

        return array_collapse($group);
    }

}
