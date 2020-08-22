<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/27/2018
 * Time: 7:44 AM
 */

namespace que\route;

use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\common\exception\RouteException;
use que\common\structure\Add;
use que\common\structure\Api;
use que\common\structure\Edit;
use que\common\structure\Info;
use que\common\structure\Page;
use que\common\structure\Receiver;
use que\common\structure\Resource;
use que\common\validator\Track;
use que\error\RuntimeError;
use que\http\HTTP;
use que\http\output\response\Html;
use que\http\output\response\Json;
use que\http\output\response\Jsonp;
use que\http\output\response\Plain;
use que\http\request\Request;
use que\security\CSRF;
use que\security\interfaces\RoutePermission;
use que\support\Arr;
use que\template\Composer;

final class Route extends RouteCompiler
{

    /**
     * @var string
     */
    private static string $method = "";

    public static function init() {

        try {

            ob_start();

            self::$method = Request::getInstance()->getMethod();

            if (!Request::getInstance()->isSupportedMethod()) {
                throw new RouteException("Sorry, '" . self::$method . "' is an unsupported request method.",
                    "Route Error", HTTP::METHOD_NOT_ALLOWED);
            }

            $uri = self::getRequestUri();

            http()->_server()->set('REQUEST_URI_ORIGINAL', $uri);

            if (!empty(APP_ROOT_FOLDER) &&
                ($start = array_search(APP_ROOT_FOLDER, $uriTokens = self::tokenizeUri($uri))) !== false) {

                $uri_extract = array_extract($uriTokens, ($start + 1));

                http()->_server()->set('REQUEST_URI', $uri = (implode("/", $uri_extract) ?: '/'));
            }

            $path = APP_PATH . DIRECTORY_SEPARATOR . $uri;

            if (str_contains($path, "#")) $path = substr($path, 0, strpos($path, "#"));

            if (str_contains($path, "?")) $path = substr($path, 0, strpos($path, "?"));

            if (is_file($path)) {
                render_file($path, pathinfo($path, PATHINFO_FILENAME));
                exit;
            }

            self::compile();

            if (is_file(APP_PATH . '/app.misc.php')) require APP_PATH . '/app.misc.php';

            self::resolve();

            self::render();

        } catch (RouteException $e) {

            RuntimeError::render(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(),
                method_exists($e, 'getTitle') ? ($e->getTitle() ?: "Route Error") : "Route Error",
                $e->getCode() ?: HTTP::INTERNAL_SERVER_ERROR);
        }
    }

    private static function render() {

        try {

            $route = self::getCurrentRoute();

            if (!empty($route)) {

                if (empty($module = $route->getModule()))  throw new RouteException(
                    "This route is not bound to a module\n", "Route Error", HTTP::NOT_FOUND);

                if (!class_exists($module, true)) throw new RouteException(
                        sprintf("The module [%s] bound to this route does not exist\n", $module),
                        "Route Error", HTTP::NOT_FOUND);

                switch ($route->getType()) {
                    case "web":
                        self::render_web_route($route);
                        break;
                    case "api":
                        self::render_api_route($route);
                        break;
                    case "resource":
                        self::render_resource_route($route);
                        break;
                    default:
                        throw new RouteException(sprintf("%s is an invalid url", current_url()), "Route Error", HTTP::NOT_FOUND);
                }

            } else throw new RouteException(sprintf("%s is an invalid url", current_url()), "Route Error", HTTP::NOT_FOUND);

        } catch (RouteException $e) {

            RuntimeError::render(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(),
                method_exists($e, 'getTitle') ? ($e->getTitle() ?: "Route Error") : "Route Error",
                $e->getCode() ?: HTTP::INTERNAL_SERVER_ERROR);

        }

    }

    /**
     * @param RouteEntry $route
     */
    private static function render_web_route(RouteEntry $route) {

        $http = http();

        try {

            $module = $route->getModule();
            $instance = new $module();

            if ($instance instanceof RoutePermission && !$instance->hasPermission($route))
                throw new RouteException("You don't have permission to this route\n",
                    "Access Denied", HTTP::UNAUTHORIZED);

            if ($instance instanceof Add) {

                if (self::$method === "GET") {
                    if ($route->isForbidCSRF() === true) CSRF::getInstance()->generateToken();
                    $instance->onLoad($http->input());
                } else {
                    if ($route->isForbidCSRF() === true) RouteInspector::validateCSRF();
                    $instance->onReceive($http->input());
                }

            } elseif ($instance instanceof Edit) {

                if (self::$method === "GET") {
                    if ($route->isForbidCSRF() === true) CSRF::getInstance()->generateToken();
                    $instance->onLoad($http->input(), $instance->info($http->input()));
                } else {
                    if ($route->isForbidCSRF() === true) RouteInspector::validateCSRF();
                    $instance->onReceive($http->input(), $instance->info($http->input()));
                }

            } elseif ($instance instanceof Info) {

                if (self::$method === "GET") {
                    if ($route->isForbidCSRF() === true) CSRF::getInstance()->generateToken();
                    $instance->onLoad($http->input(), $instance->info($http->input()));
                } else {

                    if (!$instance instanceof Receiver)
                        throw new RouteException(sprintf(
                            "The module bound to this route is not compatible with the %s request method.\n Compatible method: GET",
                            self::$method), "Route Error", HTTP::METHOD_NOT_ALLOWED);

                    if ($route->isForbidCSRF() === true) RouteInspector::validateCSRF();
                    $instance->onReceive($http->input(), $instance->info($http->input()));
                }

            } elseif ($instance instanceof Page) {

                if (self::$method === "GET") {
                    if ($route->isForbidCSRF() === true) CSRF::getInstance()->generateToken();
                    $instance->onLoad($http->input());
                } else {

                    if (!$instance instanceof Receiver)
                        throw new RouteException(sprintf(
                            "The module bound to this route is not compatible with the %s request method.\n Compatible method: GET",
                            self::$method), "Route Error", HTTP::METHOD_NOT_ALLOWED);

                    if ($route->isForbidCSRF() === true) RouteInspector::validateCSRF();
                    $instance->onReceive($http->input());
                }

            } else throw new RouteException(
                "The module bound to this route is registered\n as a web module but does not implement \n" .
                "a valid web module interface\n"
            );

            $instance->setTemplate(Composer::getInstance());

        } catch (RouteException $e) {

            RuntimeError::render(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(),
                method_exists($e, 'getTitle') ? ($e->getTitle() ?: "Route Error") : "Route Error",
                $e->getCode() ?: HTTP::INTERNAL_SERVER_ERROR);

        }
    }

    /**
     * @param RouteEntry $route
     */
    private static function render_api_route(RouteEntry $route) {

        $http = http();
        $http->_header()->set('Content-Type', mime_type_from_extension('json'), true);

        try {

            $module = $route->getModule();
            $instance = new $module();

            if ($instance instanceof RoutePermission && !$instance->hasPermission($route))
                throw new RouteException("You don't have permission to this route\n",
                    "Access Denied", HTTP::UNAUTHORIZED);

            if (!$instance instanceof Api) throw new RouteException(
                "The module bound to this route is registered\n as an API module but does not implement \n" .
                "a valid API module interface\n"
            );

            if ($route->isForbidCSRF() === true) {
                RouteInspector::validateCSRF();
                $http->_header()->set('X-Xsrf-Token', CSRF::getInstance()->getToken());
                $http->_header()->set('X-Track-Token', Track::generateToken());
            }

            $response = $instance->process($http->input());

            if ($response instanceof Json) {
                if (!$data = $response->getJson()) throw new RouteException(
                    "Failed to output response\n", "Output Error",
                    HTTP::NO_CONTENT, PreviousException::getInstance(1));
                echo $data;
            } elseif ($response instanceof Jsonp) {
                if (!$data = $response->getJsonp()) throw new RouteException(
                    "Failed to output response\n", "Output Error",
                    HTTP::NO_CONTENT, PreviousException::getInstance(1));
                $http->_header()->set('Content-Type', mime_type_from_extension('js'), true);
                echo $data;
            } elseif ($response instanceof Html) {
                $http->_header()->set('Content-Type', mime_type_from_extension('html'), true);
                echo $response->getHtml();
            } elseif ($response instanceof Plain) {
                $http->_header()->set('Content-Type', mime_type_from_extension('txt'), true);
                echo $response->getData();
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

            } else throw new RouteException(
                "Sorry, the API module bound to this route did not return a valid response\n"
            );

        } catch (RouteException $e) {

            RuntimeError::render(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(),
                method_exists($e, 'getTitle') ? ($e->getTitle() ?: "Route Error") : "Route Error",
                $e->getCode() ?: HTTP::INTERNAL_SERVER_ERROR);

        }

    }

    /**
     * @param RouteEntry $route
     */
    private static function render_resource_route(RouteEntry $route) {

        try {

            $module = $route->getModule();
            $instance = new $module();

            if ($instance instanceof RoutePermission && !$instance->hasPermission($route))
                throw new RouteException("You don't have permission to this route\n",
                    "Access Denied", HTTP::UNAUTHORIZED);

            if (!$instance instanceof Resource) throw new RouteException(
                "The module bound to this route is registered\n as an resource module but does not implement \n" .
                "a valid resource module interface\n"
            );

            $http = http();

            if ($route->isForbidCSRF() === true) {
                RouteInspector::validateCSRF();
                $http->_header()->set('X-Xsrf-Token', CSRF::getInstance()->getToken());
                $http->_header()->set('X-Track-Token', Track::generateToken());
            }

            $instance->render($http->input());

        } catch (RouteException $e) {

            RuntimeError::render(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(),
                method_exists($e, 'getTitle') ? ($e->getTitle() ?: "Route Error") : "Route Error",
                $e->getCode() ?: HTTP::INTERNAL_SERVER_ERROR);

        }

    }

    /**
     * @return RouteRegistrar
     */
    public static function register() {
        return RouteRegistrar::register();
    }

    /**
     * @param string $routeName
     * @param array|null $args
     * @param bool $addBaseUrl
     * @return string
     */
    public static function getRouteUrl(string $routeName, array $args = [], bool $addBaseUrl = true): string
    {
        $routeEntry = Route::getRouteEntryFromName($routeName);
        if (!$routeEntry instanceof RouteEntry) throw new QueRuntimeException("Route [{$routeName}] not found", "Route Error",
                E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));
        if ($routeEntry->isRequireLogIn() === true && !is_logged_in()) return '#';
        if ($routeEntry->isRequireLogIn() === false && is_logged_in()) return '#';

        if (preg_match_all('/{(.*?)}/', $routeEntry->getUri(), $matches)) {

            $argParams = array_map(function ($m) {
                return trim($m, '?');
            }, $matches[1]);

            $uri = $routeEntry->getUri();

            foreach ($argParams as $arg) {
                if (str_contains($arg, ':')) {

                    $_arg = explode(':', $arg, 2);
                    if (!Arr::exists($args, $_arg[0])) throw new QueRuntimeException(
                            "Missing required parameter for [URI: {$uri}] [Param: {$_arg[0]}] [Route: {$routeEntry->getName()}]", "Route Error");

                    try {
                        RouteInspector::validateArgDataType($_arg[1], $args[$_arg[0]]);
                    } catch (RouteException $e) {
                        throw new QueRuntimeException(
                            "{$e->getMessage()} [URI: {$uri}] [Param: {$_arg[0]},'{$args[$_arg[0]]}'] [Route: {$routeEntry->getName()}]", $e->getTitle());
                    }

                    $uri = str_replace('{' . $arg . '}', $args[$_arg[0]], $uri);

                } else {

                    if (!isset($args[$arg])) throw new QueRuntimeException(
                            "Missing required parameter for [URI: {$uri}] [Param: {$arg}] [Route: {$routeEntry->getName()}]", "Route Error");

                    $uri = str_replace('{' . $arg . '}', $args[$arg], $uri);
                }
            }

            return $addBaseUrl ? base_url($uri) : $uri;
        }
        return $addBaseUrl ? base_url($routeEntry->getUri()) : $routeEntry->getUri();
    }

    /**
     * @param string $name
     * @return RouteEntry|null
     */
    public static function getRouteEntryFromName(string $name): ?RouteEntry
    {
        $routeEntries = Route::getRouteEntries();
        foreach ($routeEntries as $routeEntry) {
            if (!$routeEntry instanceof RouteEntry) continue;
            if (strcmp($routeEntry->getName(), $name) == 0) return $routeEntry;
        }
        return null;
    }

    /**
     * @param string $uri
     * @param string|null $type
     * @return RouteEntry|null
     */
    public static function getRouteEntryFromUri(string $uri, string $type = null): ?RouteEntry {

        if (str_contains($uri, $base = base_url())) $uri = trim(str_start_from($uri, $base), '/');

        $uriTokens = self::tokenizeUri($uri);
        $routeEntries = self::getRouteEntries();

        foreach ($routeEntries as $routeEntry) {
            if (!$routeEntry instanceof RouteEntry) continue;
            if (!is_null($type) && strcmp($routeEntry->getType(), $type) != 0) continue;
            if (strcmp(implode('/', $uriTokens), implode('/', $routeEntry->uriTokens)) == 0) return $routeEntry;
            elseif (empty(($routeEntry = self::getRouteEntryFromUriWithArgs($routeEntry, $uriTokens)))) continue;
            return $routeEntry;
        }

        return null;
    }

    /**
     * @param RouteEntry $routeEntry
     * @param array $uriTokens
     * @return RouteEntry|null
     */
    private static function getRouteEntryFromUriWithArgs(RouteEntry $routeEntry, array $uriTokens): ?RouteEntry {

        $routeArgs = RouteInspector::getRouteArgs($routeEntry->getUri());

        $matches = 0;

        foreach ($routeArgs as $routeArg) {

            if (str_contains($routeArg, ":")) {

                $routeArgList = explode(":", $routeArg, 2);

                $key = array_search('{' . $routeArg . '}', $routeEntry->uriTokens);

                if (!isset($uriTokens[$key]) || !isset($routeArgList[1])) break;

                if (strcmp($routeArgList[1], "any") != 0) {

                    $uriArgValue = $uriTokens[$key];

                    try {
                        RouteInspector::validateArgDataType($routeArgList[1], $uriArgValue);
                        $uriTokens[$key] = '--';
                        $matches++;
                    } catch (RouteException $e) {
                        break;
                    }

                } else {
                    $uriTokens[$key] = '--';
                    $matches++;
                }

            } else {
                $key = array_search('{' . $routeArg . '}', $routeEntry->uriTokens);
                if (!isset($uriTokens[$key])) {
                    $uriTokens[$key] = '--';
                    $matches++;
                }
            }

        }

        if (($size = array_size($routeArgs)) > 0) {
            if ($size != $matches) return null;
            $entryUri = preg_replace("/{(.*?)}/", "--", implode('/', $routeEntry->uriTokens));
            if (strcmp($entryUri, implode('/', $uriTokens)) == 0) return $routeEntry;
        }

        return null;
    }

    /**
     * @return RouteEntry[]
     */
    public static function &getRouteEntries(): array
    {
        return self::register()->getRouteEntries();
    }

    /**
     * @return string
     */
    public static function getRequestUri(): string {
        return Request::getInstance()->getUri();
    }

}
