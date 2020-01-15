<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 5/23/2018
 * Time: 11:50 AM
 */

namespace que\http\curl;

class CurlRequest extends CurlNetwork
{
    /**
     * @var CurlRequest
     */
    private static $instance;
    
    /**
     * @var string
     */
    private $url = "";

    /**
     * @var null
     */
    private $post = null;

    /**
     * @var array
     */
    private $header = [];

    /**
     * @var int
     */
    private $timeout = 60;

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
     * @return CurlRequest
     */
    public static function getInstance(): CurlRequest
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
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    /**
     * @return null
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param null $post
     */
    public function setPost($post)
    {
        $this->post = $post;
    }

    /**
     * @return array
     */
    public function getHeader(): array
    {
        return $this->header;
    }

    /**
     * @param array $header
     */
    public function setHeader(array $header)
    {
        $this->header = $header;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @return CurlResponse
     */
    public function connect() {
        return new CurlResponse($this->fetch($this->getUrl(),
            $this->getPost(), $this->getHeader(), $this->getTimeout()));
    }


}