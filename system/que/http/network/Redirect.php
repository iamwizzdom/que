<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 9/9/2018
 * Time: 8:46 AM
 */

namespace que\http\network;

use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\http\HTTP;
use que\session\Session;

class Redirect
{
    /**
     * @var Redirect
     */
    private static Redirect $instance;

    /**
     * @var bool
     */
    private bool $preventRedirect = false;

    /**
     * @var string
     */
    private string $url = "";

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
     * @return Redirect
     */
    public static function getInstance(): Redirect
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return Redirect
     */
    public function setUrl(string $url)
    {
        if ($this->isPreventRedirect()) return $this;
        $this->url = base_url($url, true);
        return $this;
    }

    /**
     * @param string $name
     * @param array $args
     * @return Redirect
     */
    public function setRouteName(string $name, array $args = [])
    {
        return $this->setUrl(route_uri($name, $args));
    }

    /**
     * @return array
     */
    public function getHeader(): array
    {
        return Session::getInstance()->getFiles()->get("http.header", []);
    }

    /**
     * @param string $message
     * @param int $status
     * @return Redirect
     */
    public function setHeader(string $message, int $status)
    {
        if ($this->isPreventRedirect()) return $this;;
        $header = Session::getInstance()->getFiles()->get("http.header", []);
        $header[] = ['message' => $message, 'status' => $status];
        Session::getInstance()->getFiles()->set("http.header", $header);
        return $this;
    }

    /**
     * @param array $headers
     * @return Redirect
     */
    public function setHeaderArray(array $headers) {
        if ($this->isPreventRedirect()) return $this;
        if (count($headers) > 0) {
            foreach ($headers as $header) {

                if (!is_array($header)) {
                    throw new QueRuntimeException(
                        'The HTTP/Redirect setHeaderArray method expects ' .
                        'its "$headers" param to be an array list',
                        "HTTP Redirect Error", E_USER_ERROR,
                        0, PreviousException::getInstance());
                }

                if (!isset($header['message']) || !isset($header['status'])) {
                    throw new QueRuntimeException(
                        'The HTTP/Redirect setHeaderArray method expects ' .
                        'its "$headers" param to be an array list with each entry having a "message" and "status" index',
                        "HTTP Redirect Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance());
                }

                $headers = Session::getInstance()->getFiles()->get("http.header", []);
                $headers[] = $header;
                Session::getInstance()->getFiles()->set("http.header", $headers);

            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return Session::getInstance()->getFiles()->get("http.data", []);
    }

    /**
     * @param $key
     * @param $data
     * @return Redirect
     */
    public function setData($key, $data)
    {
        if ($this->isPreventRedirect()) return $this;
        Session::getInstance()->getFiles()->set("http.data.{$key}",$data);
        return $this;
    }

    public function initiate() {
        if ($this->isPreventRedirect()) return;
        if (!str__contains(($current_url = current_url()), 'logout'))
            Session::getInstance()->getFiles()->set("http.referer", $current_url);
        http()->_header()->set('Location', $this->getUrl());
        die();
    }

    /**
     * @return bool
     */
    private function isPreventRedirect(): bool
    {
        return $this->preventRedirect;
    }

    /**
     * @param bool $preventRedirect
     */
    public function setPreventRedirect(bool $preventRedirect): void
    {
        $this->preventRedirect = $preventRedirect;
    }

}
