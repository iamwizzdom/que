<?php

namespace que\route;

use Exception;
use JetBrains\PhpStorm\Pure;
use que\common\exception\RouteException;
use que\http\HTTP;
use que\http\input\Input;
use que\security\ApiMiddleware;
use que\security\interfaces\Middleware;
use que\security\GlobalMiddleware;
use que\security\ResourceMiddleware;
use que\security\WebMiddleware;

abstract class RouteInspector
{

    /**
     * @param RouteEntry $entry
     * @return array
     */
    public static function getRouteArgs(RouteEntry $entry): array
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
    public static function routeHasArgs(RouteEntry $entry): bool
    {
        return preg_match('/{(.*?)}/', $entry->getUri()) == 1;
    }

    /**
     * @param $token1
     * @param $token2
     * @return bool
     */
    #[Pure] protected static function matchTokens($token1, $token2): bool
    {
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
    public static function validateArgDataType(string $regex, $value, int $position, bool $nullable = false): void
    {

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
                    $expected[] = $expect ?: "regex $expression";
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
     * @return mixed
     * @throws RouteException
     * @throws Exception
     */
    public static function handleRouteMiddleware(RouteEntry $route): mixed
    {
        $middlewares = self::getGlobalMiddlewares();

        self::addRouteMiddlewares($route, $middlewares);

        if (!empty($middlewares)) {

            $index = 0;
            $next = function () use (&$index) {
                $index++;
            };

            function handler ($middlewares, Input $input, &$index, $next) {
                $currentIndex = $index;
                try {
                    $middleware = $middlewares[$index] ?? null;
                    if (empty($middleware)) {
                        return null;
                    }
                    $response = $middleware->handle($input, $next);
                    if ($index === $currentIndex) {
                        $index = 0;
                        return $response;
                    }
                    return handler($middlewares, $input, $index, $next) ?: $response;
                } catch (Exception $e) {
                    $index = 0;
                    throw $e;
                }
            };

            return handler($middlewares, \http()->input(), $index, $next);
        }
        return null;
    }

    /**
     * @return array
     * @throws RouteException
     */
    private static function getGlobalMiddlewares(): array {

        $globalMiddlewares = (array) config('middleware.global', []);

        $list = [];

        foreach ($globalMiddlewares as $middleware) {

            $middleware = new $middleware();

            if ($middleware instanceof GlobalMiddleware) {
                $list[] = $middleware;
                continue;
            }

            throw new RouteException("The registered global middleware [$middleware] is invalid",
                "Middleware Error", HTTP::INTERNAL_SERVER_ERROR);
        }

        return $list;
    }

    /**
     * @param RouteEntry $route
     * @param array $middlewares
     * @return void
     * @throws RouteException
     */
    private static function addRouteMiddlewares(RouteEntry $route, array &$middlewares): void
    {

        $routeMiddleware = (array) config('middleware.route', []);

        foreach ($route->getMiddleware() as $target) {

            if (!isset($routeMiddleware[$target]))
                throw new RouteException("The target route middleware [$target] does not exist",
                    "Middleware Error", HTTP::INTERNAL_SERVER_ERROR);

            $middleware = new $routeMiddleware[$target]();

            if ($route->getType() == 'web' && $middleware instanceof WebMiddleware) {
                $middlewares[] = $middleware;
                continue;
            }

            if ($route->getType() == 'api' && $middleware instanceof ApiMiddleware) {
                $middlewares[] = $middleware;
                continue;
            }

            if ($route->getType() == 'resource' && $middleware instanceof ResourceMiddleware) {
                $middlewares[] = $middleware;
                continue;
            }

            if ($middleware instanceof Middleware) {
                $middlewareType = self::getMiddlewareType($middleware);
                throw new RouteException(
                    "Sorry, you cannot bind $middlewareType middleware [$target] to {$route->getType()} route",
                    "Middleware Error", HTTP::INTERNAL_SERVER_ERROR
                );
            }

            throw new RouteException("The target route middleware [$target] is invalid",
                "Middleware Error", HTTP::INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * @param Middleware $middleware
     * @return string
     */
    private static function getMiddlewareType(Middleware $middleware): string
    {
        if ($middleware instanceof WebMiddleware) {
            return 'web';
        }

        if ($middleware instanceof ApiMiddleware) {
            return 'API';
        }

        if ($middleware instanceof ResourceMiddleware) {
            return 'resource';
        }
        return 'unknown';
    }
}
