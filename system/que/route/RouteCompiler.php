<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/27/2018
 * Time: 7:44 AM
 */

namespace que\route;

use que\http\input\Input;
use que\security\interfaces\Middleware;
use que\security\MiddlewareResponse;
use que\support\Arr;
use function http;
use que\common\exception\RouteException;
use que\error\RuntimeError;

class RouteCompiler
{

    /**
     * Here we compile all registered routes
     */
    protected static function compile() {

        self::sortRoutes(Route::getRouteEntries());
    }

    /**
     * Here we resolve the route registered for the current uri if any
     */
    protected static function resolve() {

        $routeEntries = Route::getRouteEntries();

        $routeInspector = RouteInspector::getInstance();

        $routeInspector->setUriTokens($uriTokens = self::tokenizeUri(Route::getRequestUri()));

        foreach ($routeEntries as $routeEntry) {

            if (!$routeEntry instanceof RouteEntry) continue;

            $routeInspector->setRouteEntry($routeEntry);
            $routeInspector->inspect();
        }

        try {

            $routeEntry = null; $routeArgs = []; $error = ''; $code = HTTP_OK; $percentage = 0;

            $foundRoutes = $routeInspector->getFoundRoutes();

            foreach ($foundRoutes as $foundRoute) {

                if (!array_key_exists('error', $foundRoute)) continue;
                if (($foundRoute['percentage'] ?? 0) <= $percentage) continue;

                $percentage = $foundRoute['percentage'] ?? 0;

                if (empty($foundRoute['error'])) {

                    $routeEntry = $foundRoute['routeEntry'] ?? null;
                    $routeArgs = $foundRoute['args'] ?? [];

                    if ($routeEntry instanceof RouteEntry) {

                        $args = RouteInspector::getRouteArgs($routeEntry->getUri());

                        foreach ($args as $arg) {
                            $key = array_search('{' . $arg . '}', $routeEntry->uriTokens);
                            if (array_key_exists($key, $uriTokens))
                                $uriTokens[$key] = $routeEntry->uriTokens[$key];
                        }

                        if (implode('--', $uriTokens) != implode('--', $routeEntry->uriTokens)) $routeEntry = null;
                    }

                } else {
                    $code = $foundRoute['code'] ?? HTTP_NOT_FOUND;
                    $error = $foundRoute['error'] ?? 'An unexpected error occurred while resolving the current route';
                }
            }

            if ($routeEntry === null || !$routeEntry instanceof RouteEntry) {

                if (empty($error)) throw new RouteException(sprintf("%s is an invalid url", current_url()), "Route Error", HTTP_NOT_FOUND);
                else throw new RouteException($error, "Route Error", $code);
            }

            self::setRouteParams($routeArgs);
            self::setCurrentRoute($routeEntry);

            if ($routeEntry->isUnderMaintenance() === true) {

                throw new RouteException("This route is currently under maintenance, please try again later",
                    "Access Denied", HTTP_MAINTENANCE);
            }

            if ($routeEntry->isRequireLogIn() === true && !is_logged_in()) {

                if (!empty($routeEntry->getRedirectUrl())) {

                    redirect($routeEntry->getRedirectUrl(), [
                        [
                            'message' => sprintf(
                                "You don't have access to this route (%s), login and try again.",
                                current_url()),
                            'status' => INFO
                        ]
                    ]);

                } else {
                    throw new RouteException("You don't have access to the current route, login and try again.",
                        "Access Denied", HTTP_UNAUTHORIZED);
                }

            } elseif ($routeEntry->isRequireLogIn() === false && is_logged_in()) {

                if (!empty($routeEntry->getRedirectUrl())) {

                    redirect($routeEntry->getRedirectUrl(), [
                        [
                            'message' => sprintf(
                                "You don't have access to this route (%s), logout and try again.",
                                current_url()),
                            'status' => INFO
                        ]
                    ]);

                } else {
                    throw new RouteException("You don't have access to the current route, logout and try again.",
                        "Access Denied", HTTP_UNAUTHORIZED);
                }

            }

            if ($routeEntry->getMiddleware() !== null) {

                $middleware = Arr::get(config("middleware", []), $routeEntry->getMiddleware());

                if (!empty($middleware) && (class_exists($middleware, true) && in_array(
                        Middleware::class, class_implements($middleware, true)))) {

                    $middleware = new $middleware();

                    if ($middleware instanceof Middleware) {

                        $response = new MiddlewareResponse();

                        $middleware->handle(Input::getInstance(), $response);

                        if ($response->hasAccess() === false) {
                            throw new RouteException("MIDDLEWARE CONSTRAINT: " .
                                ($response->getResponseMessage() ?: "You don't have access to the current route (" . current_url() . ")"),
                                "Access Denied", HTTP_UNAUTHORIZED);
                        }
                        return;
                    }
                }

                throw new RouteException(
                    "This route is registered with a middleware [Key: {$routeEntry->getMiddleware()}]," .
                    " but a valid middleware was not found with that key.",
                    "Middleware Error", HTTP_EXPECTATION_FAILED);
            }

        } catch (RouteException $e) {
            RuntimeError::render(E_USER_NOTICE, $e->getMessage(), $e->getFile(),
                $e->getLine(), $e->getTrace(), $e->getTitle(), $e->getCode() ?: HTTP_UNAUTHORIZED);
        }
    }

    /**
     * @param RouteEntry $routeEntry
     */
    protected static function setCurrentRoute(RouteEntry $routeEntry)
    {
        http()->_server()->set('route.entry', $routeEntry);
    }

    /**
     * @param array $uriArgs
     */
    protected static function setRouteParams(array $uriArgs) {
        http()->_server()->set("route.params", $uriArgs);
    }

    /**
     * @param array $routes
     */
    protected static function sortRoutes(array &$routes) {

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

    /**
     * @return RouteEntry
     */
    public static function getCurrentRoute()
    {
        return http()->_server()->get('route.entry', null);
    }

    /**
     * @param string $uri
     * @return array
     */
    protected static function tokenizeUri(string $uri): array {

        $tokens = []; $args = [];

        if (preg_match_all("/\{(.*?)\}/", $uri, $matches)) {
            $args = array_map(function ($m) {
                return '{' . trim($m, '?') . '}';
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

        $uri_arr = array_filter(explode("/", $uri), 'str_strip_excess_whitespace');

        foreach ($uri_arr as $token) {
            if ($token != " " || $token == "0" || !empty($token)) {
                $tokens[] = $args[$token] ?? $token;
            }
        }

        return $tokens;
    }

}