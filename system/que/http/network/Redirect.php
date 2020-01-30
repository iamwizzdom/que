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
use que\session\Session;

class Redirect
{
    /**
     * @var Redirect
     */
    private static $instance;

    /**
     * @var bool
     */
    private $preventRedirect = false;

    /**
     * @var string
     */
    private $url = "";

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
        $this->url = base_url($url);
        return $this;
    }

    /**
     * @return array
     */
    public function getHeader(): array
    {
        return (isset(Session::getInstance()->getFiles()->_get()['http']['http-header']) ?
            Session::getInstance()->getFiles()->_get()['http']['http-header'] : []);
    }

    /**
     * @param string $message
     * @param int $status
     * @return Redirect
     */
    public function setHeader(string $message, int $status)
    {
        if ($this->isPreventRedirect()) return $this;
        Session::getInstance()->getFiles()->_get()['http']['http-header'][] = ['message' => $message, 'status' => $status];
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
                        'The Http/Redirect setHeaderArray method expects ' .
                        'its "$headers" param to be an array list',
                        "Http redirect error", E_USER_ERROR,
                        0, PreviousException::getInstance(debug_backtrace()));
                }

                if (!isset($header['message']) || !isset($header['status'])) {
                    throw new QueRuntimeException(
                        'The Http/Redirect setHeaderArray method expects ' .
                        'its "$headers" param to be an array list with each entry having a "message" and "status" index',
                        "Http redirect error", E_USER_ERROR, 0, PreviousException::getInstance(debug_backtrace()));
                }

                Session::getInstance()->getFiles()->_get()['http']['http-header'][] = $header;

            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return (isset(Session::getInstance()->getFiles()->_get()['http']['http-data']) ?
            Session::getInstance()->getFiles()->_get()['http']['http-data'] : []);
    }

    /**
     * @param $key
     * @param $data
     * @return Redirect
     */
    public function setData($key, $data)
    {
        if ($this->isPreventRedirect()) return $this;
        Session::getInstance()->getFiles()->_get()['http']['http-data'][$key] = $data;
        return $this;
    }

    public function initiate() {
        if ($this->isPreventRedirect()) return;
        header("Location: {$this->getUrl()}");
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