<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/27/2018
 * Time: 7:44 AM
 */

namespace que\route;

use JetBrains\PhpStorm\Pure;
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
use que\error\RuntimeError;
use que\http\HTTP;
use que\http\output\response\Html;
use que\http\output\response\Json;
use que\http\output\response\Jsonp;
use que\http\output\response\Plain;
use que\http\request\Request;
use que\security\interfaces\RoutePermission;
use que\template\Composer;

final class Route extends Router
{
    private static ?Route $instance = null;

    protected function __construct()
    {
    }

    /**
     * @return Route|null
     */
    private static function getInstance(): ?Route
    {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

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
    public static function init()
    {

        try {

            ob_start();

            self::$method = (string) Request::getMethod();

            if (!Request::isSupportedMethod()) {
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

            if (str__contains($path, "#")) $path = substr($path, 0, strpos($path, "#"));

            if (str__contains($path, "?")) $path = substr($path, 0, strpos($path, "?"));

            if (is_file($path)) {
                render_file($path, pathinfo($path, PATHINFO_FILENAME));
                exit;
            }

            if (is_file(APP_PATH . '/app.misc.php')) require APP_PATH . '/app.misc.php';

            self::getInstance()->render();

        } catch (RouteException $e) {

            RuntimeError::render(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(),
                method_exists($e, 'getTitle') ? ($e->getTitle() ?: "Route Error") : "Route Error",
                $e->getCode() ?: HTTP::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @throws RouteException
     */
    public function render()
    {
        $route = self::resolveRoute(server('REQUEST_URI'));

        if (empty($route) || !$route instanceof RouteEntry)
            throw new RouteException(sprintf("%s is an invalid url", current_url()), "Route Error", HTTP::NOT_FOUND);

        self::handleRequestMiddleware($route);

        $origins = $route->getAllowedOrigins();

        self::$http->_header()->setBulk([
            'Access-Control-Allow-Origin' => (in_array('*', $origins) ? '*' : (in_array(Request::getOrigin(), $origins) ? Request::getOrigin() : current($origins))),
            'Access-Control-Allow-Methods' => implode(", ", $route->getAllowedMethods()),
            'Cache-Control' => 'no-cache, must-revalidate',
            'Expires' => 'Mon, 26 Jul 1997 05:00:00 GMT'
        ], false);

        $contentType = self::$http->_header()->get("Accept", $route->getContentType());
        if (!empty($contentType)) self::$http->_header()->set('Content-Type', $contentType, true);

        if (empty($module = $route->getModule())) throw new RouteException(
            "This route is not bound to a module\n", "Route Error", HTTP::NOT_FOUND);

        if (!class_exists($module, true)) throw new RouteException(
            sprintf("The module [%s] bound to this route does not exist\n", $module),
            "Route Error", HTTP::NOT_FOUND);

        switch ($route->getType()) {
            case "web":
                $this->render_web_route($route);
                break;
            case "api":
                $this->render_api_route($route);
                break;
            case "resource":
                $this->render_resource_route($route);
                break;
            default:
                throw new RouteException(sprintf("%s has an unsupported route type", current_url()), "Route Error", HTTP::NOT_FOUND);
        }

    }


    /**
     * @param RouteEntry $route
     * @throws RouteException
     */
    private function render_web_route(RouteEntry $route)
    {

        $module = $route->getModule();
        $instance = new $module();

        if ($instance instanceof RoutePermission && !$instance->hasPermission($route))
            throw new RouteException("You don't have permission to this route",
                "Access Denied", HTTP::UNAUTHORIZED);

        switch (true) {
            case $instance instanceof Add:
            case $instance instanceof Edit:
            case $instance instanceof Info:
            case $instance instanceof Page:

                if ($method = $route->getModuleMethod()) {

                    if (!method_exists($instance, $method)) throw new RouteException(
                        "The {$this->getClassName($instance)}::{$method} method bound to this route does not exist.",
                        "Route Error", HTTP::NOT_FOUND);

                    $instance->{$method}(self::$http->input());

                } else {

                    if ($instance instanceof Add) {

                        if (self::$method === "GET") $instance->onLoad(self::$http->input());
                        else $instance->onReceive(self::$http->input());

                    } elseif ($instance instanceof Edit) {

                        if (self::$method === "GET") $instance->onLoad(self::$http->input(), $instance->info(self::$http->input()));
                        else $instance->onReceive(self::$http->input(), $instance->info(self::$http->input()));

                    } elseif ($instance instanceof Info) {

                        if (self::$method === "GET") $instance->onLoad(self::$http->input(), $instance->info(self::$http->input()));
                        else {

                            if (!$instance instanceof Receiver) throw new RouteException(sprintf(
                                "The module bound to this route is not compatible with the %s request method. Compatible method: GET",
                                self::$method), "Route Error", HTTP::METHOD_NOT_ALLOWED);

                            $instance->onReceive(self::$http->input(), $instance->info(self::$http->input()));
                        }

                    } elseif ($instance instanceof Page) {

                        if (self::$method === "GET") $instance->onLoad(self::$http->input());
                        else {

                            if (!$instance instanceof Receiver) throw new RouteException(sprintf(
                                "The module bound to this route is not compatible with the %s request method. Compatible method: GET",
                                self::$method), "Route Error", HTTP::METHOD_NOT_ALLOWED);

                            $instance->onReceive(self::$http->input());
                        }

                    }
                }

                break;
            default:
                throw new RouteException(
                    "The module bound to this route is registered as a web module but does not implement " .
                    "a valid web module interface"
                );
        }

        $instance->setTemplate(Composer::getInstance());
    }


    /**
     * @param RouteEntry $route
     * @throws RouteException
     */
    private function render_api_route(RouteEntry $route)
    {

        $module = $route->getModule();
        $instance = new $module();

        if ($instance instanceof RoutePermission && !$instance->hasPermission($route))
            throw new RouteException("You don't have permission to this route",
                "Access Denied", HTTP::UNAUTHORIZED);

        if (!$instance instanceof Api) throw new RouteException(
            "The module bound to this route is registered as an API module but does not implement " .
            "a valid API module interface"
        );

        if ($method = $route->getModuleMethod()) {

            if (!method_exists($instance, $method)) throw new RouteException(
                "The {$this->getClassName($instance)}::{$method} method bound to this route does not exist.",
                "Route Error", HTTP::NOT_FOUND);

            $response = $instance->{$method}(self::$http->input());

        } else $response = $instance->process(self::$http->input());

        if ($response instanceof Json) {

            if (!$data = $response->getJson()) throw new RouteException(
                "Failed to output response", "Output Error",
                HTTP::NO_CONTENT, PreviousException::getInstance(1));

            echo $data;

        } elseif ($response instanceof JsonSerializable) {

            if (!$data = Json::encode($response)) throw new RouteException(
                "Failed to output response", "Output Error",
                HTTP::NO_CONTENT, PreviousException::getInstance(1));

            if (!self::$http->_header()->has('Accept')) self::$http->_header()->set('Content-Type', mime_type_from_extension('json'), true);

            echo $data;

        } elseif ($response instanceof Jsonp) {

            if (!$data = $response->getJsonp()) throw new RouteException(
                "Failed to output response", "Output Error",
                HTTP::NO_CONTENT, PreviousException::getInstance(1));

            self::$http->_header()->set('Content-Type', mime_type_from_extension('js'), true);
            echo $data;

        } elseif ($response instanceof Html) {

            if (!self::$http->_header()->has('Accept')) self::$http->_header()->set('Content-Type', mime_type_from_extension('html'), true);
            echo $response->getHtml();

        } elseif ($response instanceof Plain) {

            if (!self::$http->_header()->has('Accept')) self::$http->_header()->set('Content-Type', mime_type_from_extension('txt'), true);
            echo $response->getData();

        } elseif (is_array($response)) {

            if (isset($response['code']) && is_numeric($response['code']))
                self::$http->http_response_code(intval($response['code']));

            $option = Json::DEFAULT_OPTION;
            $depth = Json::DEFAULT_DEPTH;

            if (isset($response['option']) && is_numeric($response['option'])) {
                $option = intval($response['option']);
                unset($response['option']);
            }

            if (isset($response['depth']) && is_numeric($response['depth'])) {
                $depth = intval($response['depth']);
                unset($response['depth']);
            }

            $data = Json::encode($response, $option, $depth);

            if (!$data) throw new RouteException("Failed to output response", "Output Error",
                HTTP::NO_CONTENT, PreviousException::getInstance(1));

            if (!self::$http->_header()->has('Accept')) self::$http->_header()->set('Content-Type', mime_type_from_extension('json'), true);

            echo $data;

        } else throw new RouteException(
            "Sorry, the API module bound to this route did not return a valid response"
        );

    }


    /**
     * @param RouteEntry $route
     * @throws RouteException
     */
    private function render_resource_route(RouteEntry $route)
    {

        $module = $route->getModule();
        $instance = new $module();

        if ($instance instanceof RoutePermission && !$instance->hasPermission($route))
            throw new RouteException("You don't have permission to this route",
                "Access Denied", HTTP::UNAUTHORIZED);

        if (!$instance instanceof Resource) throw new RouteException(
            "The module bound to this route is registered\n as an resource module but does not implement \n" .
            "a valid resource module interface\n"
        );

        if ($method = $route->getModuleMethod()) {

            if (!method_exists($instance, $method)) throw new RouteException(
                "The {$this->getClassName($instance)}::{$method} method bound to this route does not exist.",
                "Route Error", HTTP::NOT_FOUND);

            $instance->{$method}(self::$http->input());

        } else $instance->render(self::$http->input());

    }

    /**
     * @param object $instance
     * @return string
     */
    #[Pure] private function getClassName(object $instance): string
    {
        $name = get_class($instance);
        return $name ? $name : '';
    }

    /**
     * @return string
     */
    public static function getRequestUri(): string
    {
        return Request::getInstance()->getUri();
    }

}
