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
    private static CurlRequest $instance;

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
     * @return CurlResponse
     */
    public function _exec() {
        return new CurlResponse($this->exec());
    }


}
