<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 3/30/2019
 * Time: 2:33 AM
 */

namespace que\security;

use Exception;
use que\session\type\Files;
use que\session\Session;

class CSRF
{

    /**
     * @var CSRF
     */
    private static CSRF $instance;

    /**
     * @var Files
     */
    private Files $session;

    protected function __construct()
    {
        $this->session = Session::getInstance()->getFiles();
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    private function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

    /**
     * @return CSRF
     */
    public static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param string $token
     * @return bool
     */
    public function isValidToken(string $token) {
        return !empty($tk = $this->getToken()) && strcmp($token, $tk) == 0;
    }

    /**
     * @return mixed|null
     */
    public function getToken() {
        return $this->session->get("csrf-token", '');
    }

    /**
     * @return $this
     */
    public function generateToken() {

        try {
            $this->session->add("csrf-token", "csrf:" .
                wordwrap(str_shuffle(unique_id(40) . session_id() .
                    (is_logged_in() ? user(config('database.tables.user.primary_key', 'id')) : '')), 4, ":", true));
        } catch (Exception $exception) {
        }
        return $this;
    }

}