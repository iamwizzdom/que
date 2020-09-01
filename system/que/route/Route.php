<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/27/2018
 * Time: 7:44 AM
 */

namespace que\route;

use JsonSerializable;
use que\common\exception\PreviousException;
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
use que\template\Composer;

final class Route extends Router
{

    /**
     * @var string
     */
    private static string $method = "";

    /**
     * @var HTTP
     */
    private static HTTP $http;

    /**
     * This is where we turn on the lights of Que!
     */
    public static function init() {

        try {

            ob_start();

            self::$method = (string) Request::getInstance()->getMethod();

            if (!Request::getInstance()->isSupportedMethod()) {
                throw new RouteException("Sorry, '" . self::$method . "' is an unsupported request method.",
                    "Route Error", HTTP::METHOD_NOT_ALLOWED);
            }

            $uri = self::getRequestUri();

            (self::$http = \http())->_server()->set('REQUEST_URI_ORIGINAL', $uri);

            if (!empty(APP_ROOT_FOLDER) &&
                ($start = array_search(APP_ROOT_FOLDER, $uriTokens = self::tokenizeUri($uri))) !== false) {

                $uri_extract = array_extract($uriTokens, ($start + 1));

                self::$http->_server()->set('REQUEST_URI', $uri = (implode("/", $uri_extract) ?: '/'));
            }

            $path = APP_PATH . DIRECTORY_SEPARATOR . $uri;

            if (str_contains($path, "#")) $path = substr($path, 0, strpos($path, "#"));

            if (str_contains($path, "?")) $path = substr($path, 0, strpos($path, "?"));

            if (is_file($path)) {
                render_file($path, pathinfo($path, PATHINFO_FILENAME));
                exit;
            }

            if (is_file(APP_PATH . '/app.misc.php')) require APP_PATH . '/app.misc.php';

            self::render();

        } catch (RouteException $e) {

            RuntimeError::render(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(),
                method_exists($e, 'getTitle') ? ($e->getTitle() ?: "Route Error") : "Route Error",
                $e->getCode() ?: HTTP::INTERNAL_SERVER_ERROR);
        }
    }

    private static function render() {

        try {

            $route = self::resolveRoute(server('REQUEST_URI'));

            if (empty($route) || !isset($route['route']) || !$route['route'] instanceof RouteEntry)
                throw new RouteException(sprintf("%s is an invalid url", current_url()), "Route Error", HTTP::NOT_FOUND);

            self::validateRouteAccessibility($route = $route['route']);

            self::$http->_header()->setBulk([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => !empty($route->getAllowedMethods()) ? implode(
                    ", ", $route->getAllowedMethods()) : 'GET, POST, PUT, PATCH, DELETE',
                'Cache-Control' => 'no-cache, must-revalidate',
                'Expires' => 'Mon, 26 Jul 1997 05:00:00 GMT'
            ]);

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
                    throw new RouteException(sprintf("%s has an unsupported route type", current_url()), "Route Error", HTTP::NOT_FOUND);
            }

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

        self::$http->_header()->set('Content-Type', $route->getContentType(), true);

        try {

            $module = $route->getModule();
            $instance = new $module();

            if ($instance instanceof RoutePermission && !$instance->hasPermission($route))
                throw new RouteException("You don't have permission to this route\n",
                    "Access Denied", HTTP::UNAUTHORIZED);

            if ($instance instanceof Add) {

                if (self::$method === "GET") {
                    if ($route->isForbidCSRF() === true) CSRF::getInstance()->generateToken();
                    $instance->onLoad(self::$http->input());
                } else {
                    if ($route->isForbidCSRF() === true) self::validateCSRF();
                    $instance->onReceive(self::$http->input());
                }

            } elseif ($instance instanceof Edit) {

                if (self::$method === "GET") {
                    if ($route->isForbidCSRF() === true) CSRF::getInstance()->generateToken();
                    $instance->onLoad(self::$http->input(), $instance->info(self::$http->input()));
                } else {
                    if ($route->isForbidCSRF() === true) self::validateCSRF();
                    $instance->onReceive(self::$http->input(), $instance->info(self::$http->input()));
                }

            } elseif ($instance instanceof Info) {

                if (self::$method === "GET") {
                    if ($route->isForbidCSRF() === true) CSRF::getInstance()->generateToken();
                    $instance->onLoad(self::$http->input(), $instance->info(self::$http->input()));
                } else {

                    if (!$instance instanceof Receiver)
                        throw new RouteException(sprintf(
                            "The module bound to this route is not compatible with the %s request method.\n Compatible method: GET",
                            self::$method), "Route Error", HTTP::METHOD_NOT_ALLOWED);

                    if ($route->isForbidCSRF() === true) self::validateCSRF();
                    $instance->onReceive(self::$http->input(), $instance->info(self::$http->input()));
                }

            } elseif ($instance instanceof Page) {

                if (self::$method === "GET") {
                    if ($route->isForbidCSRF() === true) CSRF::getInstance()->generateToken();
                    $instance->onLoad(self::$http->input());
                } else {

                    if (!$instance instanceof Receiver)
                        throw new RouteException(sprintf(
                            "The module bound to this route is not compatible with the %s request method.\n Compatible method: GET",
                            self::$method), "Route Error", HTTP::METHOD_NOT_ALLOWED);

                    if ($route->isForbidCSRF() === true) self::validateCSRF();
                    $instance->onReceive(self::$http->input());
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

        self::$http->_header()->set('Content-Type', $route->getContentType(), true);

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
                self::validateCSRF();
                self::$http->_header()->set('X-Xsrf-Token', CSRF::getInstance()->getToken());
                self::$http->_header()->set('X-Track-Token', Track::generateToken());
            }

            $response = $instance->process(self::$http->input());

            if ($response instanceof Json) {
                if (!$data = $response->getJson()) throw new RouteException(
                    "Failed to output response\n", "Output Error",
                    HTTP::NO_CONTENT, PreviousException::getInstance(1));
                echo $data;
            } elseif ($response instanceof JsonSerializable) {
                if (!$data = $response->jsonSerialize()) throw new RouteException(
                    "Failed to output response\n", "Output Error",
                    HTTP::NO_CONTENT, PreviousException::getInstance(1));
                self::$http->_header()->set('Content-Type', mime_type_from_extension('js'), true);
                echo $data;
            } elseif ($response instanceof Jsonp) {
                if (!$data = $response->getJsonp()) throw new RouteException(
                    "Failed to output response\n", "Output Error",
                    HTTP::NO_CONTENT, PreviousException::getInstance(1));
                self::$http->_header()->set('Content-Type', mime_type_from_extension('js'), true);
                echo $data;
            } elseif ($response instanceof Html) {
                self::$http->_header()->set('Content-Type', mime_type_from_extension('html'), true);
                echo $response->getHtml();
            } elseif ($response instanceof Plain) {
                self::$http->_header()->set('Content-Type', mime_type_from_extension('txt'), true);
                echo $response->getData();
            } elseif (is_array($response)) {

                if (isset($response['code']) && is_numeric($response['code']))
                    self::$http->http_response_code(intval($response['code']));

                $option = Json::DEFAULT_OPTION; $depth = Json::DEFAULT_DEPTH;

                if (isset($response['option']) && is_numeric($response['option'])) {
                    $option = intval($response['option']);
                    unset($response['option']);
                }

                if (isset($response['depth']) && is_numeric($response['depth'])) {
                    $depth = intval($response['depth']);
                    unset($response['depth']);
                }

                $data = Json::encode($response, $option, $depth);
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

        self::$http->_header()->set('Content-Type', $route->getContentType(), true);

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

            if ($route->isForbidCSRF() === true) {
                self::validateCSRF();
                self::$http->_header()->set('X-Xsrf-Token', CSRF::getInstance()->getToken());
                self::$http->_header()->set('X-Track-Token', Track::generateToken());
            }

            $instance->render(self::$http->input());

        } catch (RouteException $e) {

            RuntimeError::render(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(),
                method_exists($e, 'getTitle') ? ($e->getTitle() ?: "Route Error") : "Route Error",
                $e->getCode() ?: HTTP::INTERNAL_SERVER_ERROR);

        }

    }

    /**
     * @return string
     */
    public static function getRequestUri(): string {
        return Request::getInstance()->getUri();
    }

}
