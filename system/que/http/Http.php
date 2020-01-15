<?php

namespace que\http;

/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 2/1/2019
 * Time: 9:44 AM
 */

use que\http\network\Redirect;
use que\http\curl\CurlRequest;
use que\http\request\Files;
use que\http\request\Get;
use que\http\request\Header;
use que\http\request\Post;
use que\http\request\Request;
use que\http\request\Server;

class Http
{
    /**
     * @var Http
     */
    private static $instance;

    /**
     * CurlRequest constructor.
     */
    protected function __construct()
    {
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    public function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

    /**
     * @return Http
     */
    public static function getInstance(): Http
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param null $default
     * @return mixed|null
     */
    public function getLastUrl($default = null)
    {
        return $this->_header()->get("Referer", $default);
    }

    /**
     * @param null $code
     * @return int
     */
    public function http_response_code($code = null)
    {
        return http_response_code($code);
    }

    /**
     * @return Redirect
     */
    public function redirect(): Redirect {
        return Redirect::getInstance();
    }

    /**
     * @return CurlRequest
     */
    public function curl_request(): CurlRequest {
        return CurlRequest::getInstance();
    }

    /**
     * @return Get
     */
    public function _get(): Get {
        return Get::getInstance();
    }

    /**
     * @return Post
     */
    public function _post(): Post {
        return Post::getInstance();
    }

    /**
     * @return Files
     */
    public function _files(): Files {
        return Files::getInstance();
    }

    /**
     * @return Server
     */
    public function _server(): Server {
        return Server::getInstance();
    }

    /**
     * @return Request
     */
    public function _request(): Request {
        return Request::getInstance();
    }

    /**
     * @return Header
     */
    public function _header(): Header {
        return Header::getInstance();
    }

}