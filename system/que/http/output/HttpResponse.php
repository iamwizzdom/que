<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/21/2020
 * Time: 8:04 PM
 */

namespace que\http\output;


use que\http\HTTP;
use que\http\output\response\Html;
use que\http\output\response\Json;
use que\http\output\response\Jsonp;
use que\http\output\response\Plain;

class HttpResponse
{


    /**
     * @var HttpResponse
     */
    private static $instance;

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
     * @return HttpResponse
     */
    public static function getInstance(): HttpResponse
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param array $data
     * @param int $status
     * @param int $jsonOption
     * @param int $jsonDepth
     * @param array $headers
     * @param bool $replaceHeaders
     * @return Json
     */
    public function json(array $data, int $status = HTTP::OK, int $jsonOption = 0,
                         int $jsonDepth = 512, array $headers = [], bool $replaceHeaders = true): Json {
        if (!isset($data['code'])) $data['code'] = $status;
        http()->http_response_code($status);
        foreach ($headers as $key => $header) http()->_header()->set($key, $header, $replaceHeaders);
        return new Json($data, $jsonOption, $jsonDepth);
    }

    /**
     * @param string $callback
     * @param array $data
     * @param int $status
     * @param int $jsonOption
     * @param int $jsonDepth
     * @param array $headers
     * @param bool $replaceHeaders
     * @return Jsonp
     */
    public function jsonp(string $callback, array $data, int $status = HTTP::OK,
                          int $jsonOption = 0, int $jsonDepth = 512,
                          array $headers = [], bool $replaceHeaders = true): Jsonp {
        if (!isset($data['code'])) $data['code'] = $status;
        http()->http_response_code($status);
        foreach ($headers as $key => $header) http()->_header()->set($key, $header, $replaceHeaders);
        return new Jsonp($callback, $data, $jsonOption, $jsonDepth);
    }

    /**
     * @param string $content
     * @param int $status
     * @param array $headers
     * @param bool $replaceHeaders
     * @return Html
     */
    public function html(string $content, int $status = HTTP::OK,
                         array $headers = [], bool $replaceHeaders = true): Html {
        http()->http_response_code($status);
        foreach ($headers as $key => $header) http()->_header()->set($key, $header, $replaceHeaders);
        return new Html($content);
    }

    /**
     * @param string $content
     * @param int $status
     * @param array $headers
     * @param bool $replaceHeaders
     * @return Plain
     */
    public function plain(string $content, int $status = HTTP::OK,
                          array $headers = [], bool $replaceHeaders = true): Plain {
        http()->http_response_code($status);
        foreach ($headers as $key => $header) http()->_header()->set($key, $header, $replaceHeaders);
        return new Plain($content);
    }
}
