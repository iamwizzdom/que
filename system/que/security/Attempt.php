<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 2/1/2019
 * Time: 10:20 AM
 */

namespace que\security;

use que\session\Session;
use que\utility\client\IP;

class Attempt
{
    /**
     * @var Attempt
     */
    private static $instance;

    /**
     * @var int
     */
    private $max_attempt;

    /**
     * @var array
     */
    private $attempts;

    /**
     * Request constructor.
     */
    protected function __construct()
    {
        $this->attempts = Session::getInstance()->getFiles();
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
     * @return Attempt
     */
    public static function getInstance(): Attempt
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param int $attempt
     */
    public function setMaxAttempt(int $attempt = 3) {
        $this->max_attempt = $attempt;
    }

    /**
     * @return int
     */
    public function getMaxAttempt() {
        return $this->max_attempt;
    }

    /**
     * Initiate attempt
     *
     * @param string $type
     */
    public function attempt($type = "default") {
        $ip_hash = $this->getIP();
        $this->attempts[$type][$ip_hash] = (isset($this->attempts[$ip_hash]) &&
        is_integer($this->attempts[$ip_hash]) ? $this->attempts[$ip_hash]++ : 1);
    }

    /**
     * @param string $type
     * @return bool
     */
    public function isMaxAttempt($type = "default") {
        return ($this->attempts[$type][$this->getIP()] >= $this->max_attempt);
    }

    /**
     * @return string
     */
    private function getIP() {
        return sha1(IP::real());
    }
}