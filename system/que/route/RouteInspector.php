<?php


namespace que\route;


use Exception;
use que\common\exception\PreviousException;
use que\common\exception\QueException;
use que\common\exception\RouteException;
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
use que\security\JWT\TokenEncoded;
use que\security\MiddlewareResponse;
use que\support\Arr;
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
        return preg_match('/{(.*?)}/', $entry->getUri(), $matches) == 1;
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

        $expect = null; $position = Num::to_word(($position + 1));

        if (strcmp($regex, "uuid") == 0) {

            if (($nullable && is_null($value)) || UUID::is_valid($value)) return;

            $value = str_ellipsis($value ?: 'null', 20);
            throw new RouteException(
                "Invalid data type found in route argument {$position} [arg: {$value}, expected: UUID]",
                "Route Error", HTTP::EXPECTATION_FAILED
            );

        } elseif (strcmp($regex, "num") == 0) {
            $regex = "/^[0-9]+$/";
            $expect = "number";
        } elseif (strcmp($regex, "alpha") == 0) {
            $regex = "/^[a-zA-Z]+$/";
            $expect = "alphabet";
        }

        if (!($nullable && is_null($value)) && !preg_match($regex, $value)) {
            $value = str_ellipsis($value ?? 'null', 20);
            $expect = $expect ?: "regex {$regex}";
            throw new RouteException(
                "Invalid data type found in route argument {$position} [arg: {$value}, expected: $expect]",
                "Route Error", HTTP::EXPECTATION_FAILED
            );
        }
    }

    /**
     * @throws RouteException
     */
    public static function validateCSRF() {

        $token = Input::getInstance()->get('X-Csrf-Token');
        if (empty($token)) {
            foreach (
                [
                    'X-CSRF-TOKEN',
                    'x-csrf-token',
                    'csrf',
                    'Csrf',
                    'CSRF'
                ] as $key
            ) {
                $token = Input::getInstance()->get($key);
                if (!empty($token)) break;
            }
        }

        try {

            CSRF::getInstance()->validateToken((!is_null($token) ? $token : ""));
            CSRF::getInstance()->generateToken();

        } catch (QueException $e) {

            CSRF::getInstance()->generateToken();
            throw new RouteException($e->getMessage(), $e->getTitle(), HTTP::EXPIRED_AUTHENTICATION);
        }

    }

    /**
     * @param RouteEntry $routeEntry
     * @throws RouteException
     */
    public static function validateRouteAccessibility(RouteEntry $routeEntry) {

        if (!empty($routeEntry->getAllowedMethods()) && !in_array(Request::getInstance()->getMethod(), $routeEntry->getAllowedMethods())) {

            throw new RouteException(
                "The ". Request::getInstance()->getMethod() . " method is not supported for this route. Supported methods: "
                . implode(", ", $routeEntry->getAllowedMethods()) . ".", "Access Denied", HTTP::EXPIRED_AUTHENTICATION);
        }

        if ($routeEntry->isUnderMaintenance() === true) throw new RouteException(
            "This route is currently under maintenance, please try again later",
            "Access Denied", HTTP::MAINTENANCE);

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

            } else throw new RouteException("You don't have access to the current route, login and try again.",
                "Access Denied", HTTP::UNAUTHORIZED);

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

            } else throw new RouteException("You don't have access to the current route, logout and try again.",
                "Access Denied", HTTP::UNAUTHORIZED);

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
    }
}
