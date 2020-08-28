<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/27/2018
 * Time: 7:44 AM
 */

namespace que\route;

use que\common\exception\PreviousException;
use que\common\validator\Track;
use que\http\HTTP;
use que\http\input\Input;
use que\http\output\response\Html;
use que\http\output\response\Json;
use que\http\output\response\Jsonp;
use que\http\output\response\Plain;
use que\http\request\Request;
use que\security\CSRF;
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

            $routeEntry = null; $routeArgs = []; $error = ''; $code = HTTP::OK; $percentage = 0;

            $foundRoutes = $routeInspector->getFoundRoutes();

            $exactRoute = array_filter($foundRoutes, function ($found) {
                return $found['percentage'] == 100;
            });

            if (!empty($exactRoute)) $foundRoutes = $exactRoute;

            foreach ($foundRoutes as $foundRoute) {

                if (!array_key_exists('error', $foundRoute)) continue;
                if (($foundRoute['percentage'] ?? 0) <= $percentage) continue;

                $percentage = $foundRoute['percentage'] ?? 0;

                $routeEntry = $foundRoute['routeEntry'] ?? null;
                $routeArgs = $foundRoute['args'] ?? [];

                if (empty($foundRoute['error'])) {

                    if ($routeEntry instanceof RouteEntry) {

                        $args = RouteInspector::getRouteArgs($routeEntry->getUri());

                        foreach ($args as $arg) {
                            $key = array_search('{' . $arg . '}', $routeEntry->uriTokens);

                            if ($key !== false && array_key_exists($key, $uriTokens)) {
                                $uriTokens[$key] = $routeEntry->uriTokens[$key];
                            } elseif ($key !== false && str_starts_with($arg, "?")) {
                                unset($routeEntry->uriTokens[$key]);
                                continue;
                            }
                        }

                        if (implode('--', $uriTokens) != implode('--', $routeEntry->uriTokens)) $routeEntry = null;
                        else $routeEntry->uriTokens = !empty($routeEntry->originalUriTokens) ? $routeEntry->originalUriTokens : $routeEntry->uriTokens;
                    }

                } else {
                    $code = $foundRoute['code'] ?? HTTP::NOT_FOUND;
                    $error = $foundRoute['error'] ?? 'An unexpected error occurred while resolving the current route';
                }
            }

            if ($routeEntry instanceof RouteEntry) {

                self::setRouteParams($routeArgs);
                self::setCurrentRoute($routeEntry);

            } else throw new RouteException(sprintf("%s is an invalid url", current_url()), "Route Error", HTTP::NOT_FOUND);

            if (!empty($error)) throw new RouteException($error, "Route Error", $code);

            http()->_header()->setBulk([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => !empty($routeEntry->getAllowedMethods()) ? implode(
                    ", ", $routeEntry->getAllowedMethods()) : 'GET, POST, PUT, PATCH, DELETE',
                'Cache-Control' => 'no-cache, must-revalidate',
                'Expires' => 'Mon, 26 Jul 1997 05:00:00 GMT',
                'Content-Type' => 'text/html'
            ]);

            if (!empty($routeEntry->getAllowedMethods()) && !in_array(Request::getInstance()->getMethod(), $routeEntry->getAllowedMethods())) {

                throw new RouteException(
                    "The ". Request::getInstance()->getMethod() . " method is not supported for this route. Supported methods: "
                    . implode(", ", $routeEntry->getAllowedMethods()) . ".", "Access Denied", HTTP::EXPIRED_AUTHENTICATION);
            }

            if ($routeEntry->isUnderMaintenance() === true) {

                throw new RouteException("This route is currently under maintenance, please try again later",
                    "Access Denied", HTTP::MAINTENANCE);
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
                        "Access Denied", HTTP::UNAUTHORIZED);
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
                        "Access Denied", HTTP::UNAUTHORIZED);
                }

            }

            if ($routeEntry->getMiddleware() !== null) {

                $middleware = Arr::get(config("middleware", []), $routeEntry->getMiddleware());

                if (!empty($middleware) && (class_exists($middleware, true) && in_array(
                        Middleware::class, class_implements($middleware, true)))) {

                    $middleware = new $middleware();

                    if ($middleware instanceof Middleware) {

                        $middlewareResponse = new MiddlewareResponse();

                        $middleware->handle(Input::getInstance(), $middlewareResponse);

                        if ($middlewareResponse->hasAccess() === false) {

                            $http = http();

                            $response = $middlewareResponse->getResponse();

                            if ($response && $routeEntry->isForbidCSRF() === true) {
                                RouteInspector::validateCSRF();
                                $http->_header()->set('X-Xsrf-Token', CSRF::getInstance()->getToken());
                                $http->_header()->set('X-Track-Token', Track::generateToken());
                            }

                            if ($response instanceof Json) {
                                if (!$data = $response->getJson()) throw new RouteException(
                                    "Failed to output response\n", "Output Error",
                                    HTTP::NO_CONTENT, PreviousException::getInstance(1));
                                $http->_header()->set('Content-Type', mime_type_from_extension('json'), true);
                                echo $data;
                                exit();
                            } elseif ($response instanceof Jsonp) {
                                if (!$data = $response->getJsonp()) throw new RouteException(
                                    "Failed to output response\n", "Output Error",
                                    HTTP::NO_CONTENT, PreviousException::getInstance(1));
                                $http->_header()->set('Content-Type', mime_type_from_extension('js'), true);
                                echo $data;
                                exit();
                            } elseif ($response instanceof Html) {
                                $http->_header()->set('Content-Type', mime_type_from_extension('html'), true);
                                echo $response->getHtml();
                                exit();
                            } elseif ($response instanceof Plain) {
                                $http->_header()->set('Content-Type', mime_type_from_extension('txt'), true);
                                echo $response->getData();
                                exit();
                            } elseif (is_array($response)) {

                                if (isset($response['code']) && is_numeric($response['code']))
                                    $http->http_response_code(intval($response['code']));

                                $option = 0; $depth = 512;

                                if (isset($response['option']) && is_numeric($response['option'])) {
                                    $option = intval($response['option']);
                                    unset($response['option']);
                                }

                                if (isset($response['depth']) && is_numeric($response['depth'])) {
                                    $depth = intval($response['depth']);
                                    unset($response['depth']);
                                }

                                $data = json_encode($response, $option, $depth);
                                if (!$data) throw new RouteException("Failed to output response\n");
                                echo $data;
                                exit();
                            }

                            throw new RouteException(
                                $middlewareResponse->getMessage() ?: ("MIDDLEWARE CONSTRAINT: You don't have access to the current route (" . current_url() . ")"),
                                "Access Denied", HTTP::UNAUTHORIZED);
                        }
                        return;
                    }
                }

                throw new RouteException(
                    "This route is registered with a middleware [Key: {$routeEntry->getMiddleware()}]," .
                    " but a valid middleware was not found with that key.",
                    "Middleware Error", HTTP::METHOD_NOT_ALLOWED);
            }

        } catch (RouteException $e) {
            RuntimeError::render(E_USER_NOTICE, $e->getMessage(), $e->getFile(),
                $e->getLine(), $e->getTrace(), $e->getTitle(), $e->getCode() ?: HTTP::UNAUTHORIZED);
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

}
