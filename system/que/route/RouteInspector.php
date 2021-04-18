<?php

namespace que\route;

use que\common\exception\PreviousException;
use que\common\exception\RouteException;
use que\http\HTTP;
use que\http\input\Input;
use que\http\output\response\Html;
use que\http\output\response\Json;
use que\http\output\response\Jsonp;
use que\http\output\response\Plain;
use que\security\interfaces\Middleware;
use que\security\MiddlewareResponse;
use que\support\Num;
use que\utility\random\UUID;

abstract class RouteInspector
{

    /**
     * @param RouteEntry $entry
     * @return array|string[]
     */
    public static function getRouteArgs(RouteEntry $entry)
    {
        if (preg_match_all("/{(.*?)}/", $entry->getUri(), $matches)) {
            return array_map(function ($m) {
                return trim($m);
            }, $matches[1]);
        }
        return [];
    }

    /**
     * @param RouteEntry $entry
     * @return bool
     */
    public static function routeHasArgs(RouteEntry $entry) {
        return preg_match('/{(.*?)}/', $entry->getUri()) == 1;
    }

    /**
     * @param $token1
     * @param $token2
     * @return bool
     */
    protected static function matchTokens($token1, $token2) {
        if (array_size($token1) != array_size($token2)) return false;
        foreach ($token1 as $key => $value) {
            if (!array_key_exists($key, $token2)) return false;
            if (strcmp($value, $token2[$key]) != 0) return false;
        }
        return true;
    }

    /**
     * @param string $arg
     * @return array|null
     */
    public static function decipherArg(string $arg): ?array {

        if (preg_match("/{(\?)(.*?):(.*?)}|{(.*?):(.*?)}|{(\?)(.*?)}|{(.*?)}/", $arg, $matches) == 1) {

            if (!empty($matches[8] ?? null)) return [
                'arg' => $matches[8],
                'nullable' => false,
                'expression' => null
            ];

            if (!empty($matches[7] ?? null) && !empty($matches[6] ?? null))
                return [
                    'arg' => $matches[7],
                    'nullable' => true,
                    'expression' => null
                ];

            if (!empty($matches[5] ?? null) && !empty($matches[4] ?? null))
                return [
                    'arg' => $matches[4],
                    'nullable' => false,
                    'expression' => $matches[5]
                ];

            if (!empty($matches[3] ?? null) && !empty($matches[2] ?? null) && !empty($matches[1] ?? null))
                return [
                    'arg' => $matches[2],
                    'nullable' => true,
                    'expression' => $matches[3]
                ];

        }

        return null;
    }

    /**
     * @param string $regex
     * @param $value
     * @param int $position
     * @param bool $nullable
     * @throws RouteException
     */
    public static function validateArgDataType(string $regex, $value, int $position, bool $nullable = false) {

        $expected = []; $position = Num::to_word(($position + 1));

        if (str__contains($regex, "|")) $regex = explode("|", $regex);
        else $regex = [$regex];

        $error_count = 0;

        foreach ($regex as $expression) {

            try {

                $expect = null;

                if (strcmp($expression, "uuid") == 0) {

                    if (($nullable && is_null($value)) || UUID::is_valid($value)) return;
                    $expected[] = "UUID";
                    throw new RouteException("Failed");

                } elseif (strcmp($expression, "num") == 0) {
                    $expression = "/^[0-9]+$/";
                    $expect = "number";
                } elseif (strcmp($expression, "alpha") == 0) {
                    $expression = "/^[a-zA-Z]+$/";
                    $expect = "alphabet";
                }

                if (!($nullable && is_null($value)) && !preg_match($expression, $value)) {
                    $expected[] = $expect ?: "regex {$expression}";
                    throw new RouteException("Failed");
                }
            } catch (RouteException $e) {
                $error_count++;
            }
        }

        if ($error_count == count($regex)) {
            $value = str_ellipsis($value ?: 'null', 20);
            $expected = implode(" or ", $expected);
            throw new RouteException(
                "Invalid data type found in route argument {$position} [arg: {$value}, expected: {$expected}]",
                "Route Error", HTTP::EXPECTATION_FAILED
            );
        }
    }

    /**
     * @param RouteEntry $route
     * @throws RouteException
     */
    public static function handleRequestMiddleware(RouteEntry $route) {

        $middlewareStack = self::getGlobalMiddlewareStack();

        $routeMiddlewares = self::getRouteMiddlewareStack($route);

        foreach ($routeMiddlewares as $routeMiddleware) $middlewareStack[] = $routeMiddleware;

        if (!empty($middlewareStack)) {

            self::linkMiddleware($middlewareStack);

            $middleware = current($middlewareStack);

            if ($middleware instanceof Middleware) {

                $middlewareResponse = $middleware->handle(Input::getInstance());

                if ($middlewareResponse instanceof MiddlewareResponse) {

                    if ($middlewareResponse->hasAccess() === false) {

                        $http = \http();

                        $response = $middlewareResponse->getResponse();

                        if ($response instanceof Json) {

                            $data = $response->getData();
                            $data['title'] = ($data['title'] ? $data['title'] : $middlewareResponse->getTitle());
                            $response->setData($data);

                            if (!$data = $response->getJson()) throw new RouteException(
                                "Failed to output response", "Output Error",
                                HTTP::NO_CONTENT, PreviousException::getInstance(1));

                            $code = $middlewareResponse->getResponseCode();
                            if (!$code) $code = $response->getData()['code'] ?? 0;
                            if (!$code) $code = http_response_code();
                            $http->http_response_code($code ?: HTTP::OK);

                            $http->_header()->set('Content-Type', mime_type_from_extension('json'), true);
                            echo $data;
                            exit();

                        } elseif ($response instanceof Jsonp) {

                            $data = $response->getData();
                            $data['title'] = ($data['title'] ? $data['title'] : $middlewareResponse->getTitle());
                            $response->setData($data);

                            if (!$data = $response->getJsonp()) throw new RouteException(
                                "Failed to output response", "Output Error",
                                HTTP::NO_CONTENT, PreviousException::getInstance(1));

                            $http->http_response_code($middlewareResponse->getResponseCode());
                            $http->_header()->set('Content-Type', mime_type_from_extension('js'), true);
                            echo $data;
                            exit();

                        } elseif ($response instanceof Html) {

                            $code = $middlewareResponse->getResponseCode();
                            if (!$code) $code = $response->getData()['code'] ?? 0;
                            if (!$code) $code = http_response_code();
                            $http->http_response_code($code ?: HTTP::OK);

                            $http->_header()->set('Content-Type', mime_type_from_extension('html'), true);
                            echo $response->getHtml();
                            exit();

                        } elseif ($response instanceof Plain) {

                            $http->_header()->set('Content-Type', mime_type_from_extension('txt'), true);
                            echo $response->getData();
                            exit();

                        } elseif (is_array($response)) {

                            if (isset($response['title'])) {
                                $response['title'] = ($response['title'] ? $response['title'] : $middlewareResponse->getTitle());
                            }

                            if (is_numeric($response['code'] ?? null)) $http->http_response_code(intval($response['code']));
                            else {
                                $code = $middlewareResponse->getResponseCode();
                                if (!$code) $code = http_response_code();
                                $http->http_response_code($code ?: HTTP::OK);
                            }

                            $option = 0; $depth = 512;

                            if (is_numeric($response['option'] ?? null)) {
                                $option = intval($response['option']);
                                unset($response['option']);
                            }

                            if (is_numeric($response['depth'] ?? null)) {
                                $depth = intval($response['depth']);
                                unset($response['depth']);
                            }

                            $data = json_encode($response, $option, $depth);
                            if (!$data) throw new RouteException("Failed to output response", "Output Error",
                                HTTP::NO_CONTENT, PreviousException::getInstance(1));

                            $http->_header()->set('Content-Type', mime_type_from_extension('js'), true);
                            echo $data;
                            exit();
                        }

                        throw new RouteException($response, $middlewareResponse->getTitle(), $middlewareResponse->getResponseCode());
                    }
                }
            }
        }
    }

    private static function linkMiddleware(array $middlewareStack) {
        $currentMiddleware = current($middlewareStack); $next = null;
        for ($i = 1; $i < count($middlewareStack); $i++) {
            if ($next === null) $next = $currentMiddleware->setNext($middlewareStack[$i]);
            else $next = $next->setNext($middlewareStack[$i]);
        }
    }

    /**
     * @return array
     * @throws RouteException
     */
    private static function getGlobalMiddlewareStack() {

        $globalMiddleware = (array) config('middleware.global', []);

        $stack = [];

        foreach ($globalMiddleware as $middleware) {

            $middleware = new $middleware();

            if ($middleware instanceof Middleware) {
                $stack[] = $middleware;
                continue;
            }

            throw new RouteException("The registered global middleware [{$middleware}] is invalid",
                "Middleware Error", HTTP::INTERNAL_SERVER_ERROR);
        }

        return $stack;
    }

    /**
     * @param RouteEntry $route
     * @return array
     * @throws RouteException
     */
    private static function getRouteMiddlewareStack(RouteEntry $route) {

        $routeMiddleware = (array) config('middleware.route', []);

        $stack = [];

        foreach ($route->getMiddleware() as $target) {

            if (!isset($routeMiddleware[$target]))
                throw new RouteException("The target route middleware [{$target}] does not exist",
                    "Middleware Error", HTTP::INTERNAL_SERVER_ERROR);

            $middleware = new $routeMiddleware[$target]();

            if ($middleware instanceof Middleware) {
                $stack[] = $middleware;
                continue;
            }

            throw new RouteException("The target route middleware [{$target}] is invalid",
                "Middleware Error", HTTP::INTERNAL_SERVER_ERROR);
        }

        return $stack;
    }
}
