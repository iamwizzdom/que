<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/21/2020
 * Time: 8:04 PM
 */

namespace que\http\output;


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
     * @param array $headers
     * @return Json
     */
    public function json(array $data, int $status = HTTP_SUCCESS_CODE, array $headers = []): Json {
        if (!isset($data['code'])) $data['code'] = $status;
        http()->http_response_code($data['code']);
        foreach ($headers as $header) header($header, true);
        return new Json($data);
    }

    /**
     * @param string $callback
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return Jsonp
     */
    public function jsonp(string $callback, array $data, int $status = HTTP_SUCCESS_CODE, array $headers = []): Jsonp {
        if (!isset($data['code'])) $data['code'] = $status;
        http()->http_response_code($data['code']);
        foreach ($headers as $header) header($header, true);
        return new Jsonp($callback, $data);
    }

    /**
     * @param string $content
     * @param int $status
     * @param array $headers
     * @return Html
     */
    public function html(string $content, int $status = HTTP_SUCCESS_CODE, array $headers = []): Html {
        http()->http_response_code($status);
        foreach ($headers as $header) header($header, true);
        return new Html($content);
    }

    /**
     * @param string $content
     * @param int $status
     * @param array $headers
     * @return Plain
     */
    public function plain(string $content, int $status = HTTP_SUCCESS_CODE, array $headers = []): Plain {
        http()->http_response_code($status);
        foreach ($headers as $header) header($header, true);
        return new Plain($content);
    }
}