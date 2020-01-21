<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/27/2018
 * Time: 7:44 AM
 */

namespace que\route;

use Exception;
use que\common\exception\RouteException;
use que\common\validate\Track;
use que\error\RuntimeError;
use que\route\structure\RouteEntry;
use que\route\structure\RouteImplementEnum;
use que\security\CSRF;
use que\template\Composer;

final class Route extends RouteCompiler
{

    public static function init() {

        $uri = self::getRequestUri();

        if (str_contains($uri, APP_ROOT_FOLDER)) {
            http()->_server()->add('REQUEST_URI_ORIGINAL', $uri);
            http()->_server()->add('REQUEST_URI', $uri = str_start_from($uri,
                APP_ROOT_FOLDER));
        }
        
        self::compile();

        self::resolve();

        self::render();
    }

    private static function render() {

        try {

            $route = self::getCurrentRoute();

            if (!empty($route)) {

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
                        throw new RouteException(sprintf("%s is an invalid url", current_url()), "Route Error", HTTP_NOT_FOUND_CODE);
                }

            } else throw new RouteException(sprintf("%s is an invalid url", current_url()), "Route Error", HTTP_NOT_FOUND_CODE);

        } catch (RouteException $e) {

            RuntimeError::render(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(), $e->getTitle(),
                $e->getCode() == HTTP_NOT_FOUND_CODE ? HTTP_NOT_FOUND_CODE : HTTP_ERROR_CODE);

        }

    }

    /**
     * @param RouteEntry $route
     */
    private static function render_web_route(RouteEntry $route) {

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header("Content-Type: text/html");

        try {

            $http = http();

            //check if JWT is required
            if ($route->isRequireJWTAuth() === true) try {
                RouteInspector::validateJWT($http);
            } catch (Exception $e) {
                throw new RouteException($e->getMessage(), "JWT Auth Error");
            }

            if (!empty($module = $route->getModule())) {

                $method = $http->_server()->get("REQUEST_METHOD");

                if (!($method === 'GET' || $method === 'POST'))
                    throw new RouteException("Sorry, ({$method} request) is an unsupported request method");

                switch ($route->getImplement()) {
                    case RouteImplementEnum::IMPLEMENT_ADD:

                        if (!class_exists($module, true))
                            throw new RouteException(sprintf(
                                "Sorry, the module (%s) bound to the current " .
                                "route \n(%s) does not exist\n", $module, current_url()));

                        $implement = class_implements($module, true);

                        if (!$implement)
                            throw new RouteException(sprintf(
                                "Sorry, the module (%s) bound to the current " .
                                "route \n(%s) does not implement a valid interface\n", $module, current_url()));

                        if (!isset($implement['que\common\structure\Add']))
                            throw new RouteException(sprintf(
                                "Sorry, the module (%s) bound to the current " .
                                "route \n(%s) implements the wrong interface (%s)\n",
                                $module, current_url(), implode(', ', $implement)));

                        $instance = new $module();

                        if (isset($implement['que\security\permission\RoutePermission']) && !$instance->hasPermission($route))
                            throw new RouteException(sprintf("You dont have permission to the current route (%s)", current_url()),
                                "Access denied", E_USER_NOTICE);

                        if ($method === "GET") {
                            if ($route->isRequireCSRFAuth() === true) CSRF::getInstance()->generateToken();
                            $instance->{"onLoad"}($http->_server()->get("URI_ARGS"));
                        } else {
                            if ($route->isRequireCSRFAuth() === true) RouteInspector::validateCSRF();
                            $instance->{"onReceive"}($http->_server()->get("URI_ARGS"));
                        }

                        $instance->setTemplate(Composer::getInstance());

                        break;
                    case RouteImplementEnum::IMPLEMENT_EDIT:

                        if (!class_exists($module, true))
                            throw new RouteException(sprintf(
                                "Sorry, the module (%s) bound to the current " .
                                "route \n(%s) does not exist\n", $module, current_url()));

                        $implement = class_implements($module, true);

                        if (!$implement)
                            throw new RouteException(sprintf(
                                "Sorry, the module (%s) bound to the current " .
                                "route (%s) does not implement a valid interface", $module, current_url()));

                        if (!isset($implement['que\common\structure\Edit']))
                            throw new RouteException(sprintf(
                                "Sorry, the module (%s) bound to the current " .
                                "route \n(%s) implements the wrong interface (%s)\n",
                                $module, current_url(), implode(', ', $implement)));

                        $instance = new $module();

                        if (isset($implement['que\security\permission\RoutePermission']) && !$instance->hasPermission($route))
                            throw new RouteException(sprintf("You dont have permission to the current route (%s)", current_url()),
                                "Access denied", E_USER_NOTICE);

                        $args = $http->_server()->get("URI_ARGS");

                        if ($method === "GET") {
                            if ($route->isRequireCSRFAuth() === true) CSRF::getInstance()->generateToken();
                            $instance->{"onLoad"}($args, $instance->{"info"}($args));
                        } else {
                            if ($route->isRequireCSRFAuth() === true) RouteInspector::validateCSRF();
                            $instance->{"onReceive"}($args, $instance->{"info"}($args));
                        }

                        $instance->setTemplate(Composer::getInstance());

                        break;
                    case RouteImplementEnum::IMPLEMENT_INFO:

                        if (!class_exists($module, true))
                            throw new RouteException(sprintf(
                                "Sorry, the module (%s) bound to the current " .
                                "route \n(%s) does not exist\n", $module, current_url()));

                        $implement = class_implements($module, true);

                        if (!$implement)
                            throw new RouteException(sprintf(
                                "Sorry, the module (%s) bound to the current " .
                                "route \n(%s) does not implement a valid interface\n", $module, current_url()));

                        if (!isset($implement['que\common\structure\Info']))
                            throw new RouteException(sprintf(
                                "Sorry, the module (%s) bound to the current " .
                                "route \n(%s) implements the wrong interface (%s)\n",
                                $module, current_url(), implode(', ', $implement)));

                        $instance = new $module();

                        if (isset($implement['que\security\permission\RoutePermission']) && !$instance->hasPermission($route))
                            throw new RouteException(sprintf("You dont have permission to the current route \n(%s)\n", current_url()),
                                "Access denied", E_USER_NOTICE);

                        $args = $http->_server()->get("URI_ARGS");

                        if ($method === "GET") {
                            if ($route->isRequireCSRFAuth() === true) CSRF::getInstance()->generateToken();
                            $instance->{"onLoad"}($args, $instance->{"info"}($args));
                        } else {
                            if ($route->isRequireCSRFAuth() === true) RouteInspector::validateCSRF();
                            $instance->{"onReceive"}($args, $instance->{"info"}($args));
                        }

                        $instance->setTemplate(Composer::getInstance());

                        break;
                    case RouteImplementEnum::IMPLEMENT_PAGE:

                        if (!class_exists($module, true))
                            throw new RouteException(sprintf(
                                "Sorry, the module (%s) bound to the current " .
                                "route \n(%s) does not exist\n", $module, current_url()));

                        $implement = class_implements($module, true);

                        if (!$implement)
                            throw new RouteException(sprintf(
                                "Sorry, the module (%s) bound to the current " .
                                "route (%s) does not implement a valid interface", $module, current_url()));

                        if (!isset($implement['que\common\structure\Page']))
                            throw new RouteException(sprintf(
                                "Sorry, the module (%s) bound to the current " .
                                "route \n(%s) implements the wrong interface (%s)\n",
                                $module, current_url(), implode(', ', $implement)));

                        $instance = new $module();

                        if (isset($implement['que\security\permission\RoutePermission']) && !$instance->hasPermission($route))
                            throw new RouteException(sprintf("You dont have permission to the current route (%s)", current_url()),
                                "Access denied", E_USER_NOTICE);

                        if ($method === "GET") {

                            if ($route->isRequireCSRFAuth() === true) CSRF::getInstance()->generateToken();
                            $instance->{"onLoad"}($http->_server()->get("URI_ARGS"));

                        } else {

                            if (!isset($implement['que\common\structure\Receiver'])) {

                                $http->http_response_code(HTTP_INTERNAL_ERROR_CODE);

                                throw new RouteException(sprintf(
                                    "Sorry, the current route (%s)\n does not support (%s request).\n",
                                    current_url(), $method));
                            }

                            if ($route->isRequireCSRFAuth() === true) RouteInspector::validateCSRF();
                            $instance->{"onReceive"}($http->_server()->get("URI_ARGS"));
                        }

                        $instance->setTemplate(Composer::getInstance());

                        break;
                    default:
                        throw new RouteException(sprintf(
                            "Sorry, the module bound to the current " .
                            "route \n(%s) does not implement a valid interface\n", current_url()));
                        break;
                }

            } else throw new RouteException(sprintf(
                "Sorry, the current route (%s)\n is not bound to a module", current_url()));

        } catch (RouteException $e) {

            RuntimeError::render(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(),
                method_exists($e, 'getTitle') ? $e->getTitle() ?: "Route Error" : "Route Error", HTTP_ERROR_CODE);

        }
    }

    /**
     * @param RouteEntry $route
     */
    private static function render_api_route(RouteEntry $route) {

        header('Access-Control-Allow-Headers: *');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header("Content-Type: application/json");

        try {

            $http = http();

            //check if JWT is required
            if ($route->isRequireJWTAuth() === true) try {
                RouteInspector::validateJWT($http);
            } catch (Exception $e) {
                throw new RouteException($e->getMessage(), "JWT Auth Error");
            }

            if (!empty($module = $route->getModule())) {

                if (!class_exists($module, true))
                    throw new RouteException(sprintf(
                        "Sorry, the module (%s) bound to the current " .
                        "route \n(%s) does not exist\n", $module, current_url()));

                $implement = class_implements($module, true);

                if (!$implement)
                    throw new RouteException(sprintf(
                        "Sorry, the module (%s) bound to the current " .
                        "route \n(%s) does not implement a valid interface\n", $module, current_url()));

                if (!isset($implement['que\common\structure\Api']))
                    throw new RouteException(sprintf(
                        "Sorry, the module (%s) bound to the current " .
                        "route (%s)\n implements the wrong interface (%s)\n",
                        $module, $route->getUri(), implode(', ', $implement)));

                $instance = new $module();

                if (isset($implement['que\security\permission\RoutePermission']) && !$instance->hasPermission($route))
                    throw new RouteException(sprintf("You dont have permission to the current route (%s)", current_url()),
                        "Access denied", E_USER_NOTICE);

                if ($route->isRequireCSRFAuth() === true) {
                    RouteInspector::validateCSRF();
                    header("X-Xsrf-Token: " . CSRF::getInstance()->getToken());
                    header("X-Track-Token: " . Track::generateToken());
                }

                $response = $instance->{"process"}($http->_server()->get("URI_ARGS"));

                if (isset($response['code']))
                    $http->http_response_code($response['code']);

                echo json_encode($response, JSON_PRETTY_PRINT);

            } else throw new RouteException(sprintf(
                "Sorry, the current route (%s) is not bound to a module", current_url()));

        } catch (RouteException $e) {

            RuntimeError::render(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(),
                method_exists($e, 'getTitle') ? $e->getTitle() ?: "Route Error" : "Route Error", HTTP_ERROR_CODE);

        }

    }

    /**
     * @param RouteEntry $route
     */
    private static function render_resource_route(RouteEntry $route) {

        header('Access-Control-Allow-Headers: *');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

        try {

            $http = http();

            //check if JWT is required
            if ($route->isRequireJWTAuth() === true) try {
                RouteInspector::validateJWT($http);
            } catch (Exception $e) {
                throw new RouteException($e->getMessage(), "JWT Auth Error");
            }

            if (!empty($module = $route->getModule())) {

                if (!class_exists($module, true))
                    throw new RouteException(sprintf(
                        "Sorry, the module (%s) bound to the current " .
                        "route \n(%s) does not exist\n", $module, current_url()));

                $implement = class_implements($module, true);

                if (!$implement)
                    throw new RouteException(sprintf(
                        "Sorry, the module (%s) bound to the current " .
                        "route (%s)\n does not implement a valid interface\n", $module, current_url()));

                if (!isset($implement['que\common\structure\Resource']))
                    throw new RouteException(sprintf(
                        "Sorry, the module (%s) bound to the current " .
                        "route (%s) \nimplements the wrong interface (%s)\n",
                        $module, current_url(), implode(', ', $implement)));

                $instance = new $module();

                if (isset($implement['que\security\permission\RoutePermission']) && !$instance->hasPermission($route))
                    throw new RouteException(sprintf("You dont have permission to the current route (%s)", current_url()),
                        "Access denied", E_USER_NOTICE);

                if ($route->isRequireCSRFAuth() === true) {
                    RouteInspector::validateCSRF();
                    header("X-Xsrf-Token: " . CSRF::getInstance()->getToken());
                    header("X-Track-Token: " . Track::generateToken());
                }

                $instance->{"render"}($http->_server()->get("URI_ARGS"));

            } else throw new RouteException(sprintf(
                "Sorry, the current route (%s) is not bound to a module", current_url()));

        } catch (RouteException $e) {

            RuntimeError::render(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(),
                method_exists($e, 'getTitle') ? $e->getTitle() ?: "Route Error" : "Route Error", HTTP_ERROR_CODE);

        }

    }

    /**
     * @return RouteRegistrar
     */
    public static function register() {
        return RouteRegistrar::register();
    }

    /**
     * @param string $uri
     * @param string|null $type
     * @return RouteEntry|null
     */
    public static function getRouteEntry(string $uri, string $type = null): ?RouteEntry {

        $uriTokens = self::tokenizeUri($uri);
        $routeEntries = self::getRouteEntries();

        foreach ($routeEntries as $routeEntry) {
            if (!$routeEntry instanceof RouteEntry) continue;
            if (!is_null($type) && strcmp($routeEntry->getType(), $type) != 0) continue;
            if (implode('--', $uriTokens) === implode('--', $routeEntry->uriTokens)) return $routeEntry;
            else {

                $routeArgs = RouteInspector::getRouteArgs($routeEntry->getUri());

                $matches = 0;

                foreach ($routeArgs as $key => $routeArg) {

                    $routeArgList = [$routeArg];

                    if (str_contains($routeArg, ":"))
                        $routeArgList = explode(":", $routeArg, 2);

                    $key = array_search('{' . $routeArg . '}', $routeEntry->uriTokens);

                    if (!isset($uriTokens[$key])) break;

                    $uriArgValue = $uriTokens[$key];

                    if (isset($routeArgList[0]) && strcmp($routeArgList[0], current(RouteInspector::getRouteArgs($uriArgValue))) == 0) $matches++;
                    elseif (isset($routeArgList[1]) && strcmp($routeArgList[1], "any") != 0) {
                        try {
                            RouteInspector::validateArgDataType($routeArgList[1], $uriArgValue);
                            $uriTokens[$key] = '--';
                            $matches++;
                        } catch (RouteException $e) {
                            break;
                        }
                    } else $matches++;

                }

                if (($size = array_size($routeArgs)) > 0 && $size == $matches) {
                    $entryUri = preg_replace("/\{(.*?)\}/", "--", implode('--', $routeEntry->uriTokens));
                    $uri = preg_replace("/\{(.*?)\}/", "--", implode('--', $uriTokens));
                    if ($entryUri === $uri) return $routeEntry;
                } elseif (implode('--', $uriTokens) === implode('--', $routeEntry->uriTokens)) return $routeEntry;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public static function &getRouteEntries(): array
    {
        return self::register()->getRouteEntries();
    }

    /**
     * @return string
     */
    public static function getRequestUri(): string {
        return http()->_server()->get("REQUEST_URI", "/");
    }

}