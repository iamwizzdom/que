<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 8/13/2018
 * Time: 2:32 PM
 */

namespace que\common\validate;


use que\http\input\Input;
use que\session\Session;

class Track
{

    /**
     * @var Track
     */
    private static $instance;

    protected function __construct()
    {
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
     * @return Track
     */
    public static function getInstance(): Track
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    public static function generateToken($prefix = "track") {
        return $prefix . ":" . wordwrap(unique_id(50), 4, ":", true);
    }

    /**
     * @param string|null $key
     * @param array $ref
     */
    public function set(string $key = null, array $ref = []){

        if (is_null($key)) {
            $key = Input::getInstance()->get('X-Track-Token');
            if (empty($key)) $key = Input::getInstance()->get('track');
        }

        $ref = empty($ref) ? post() : $ref;
        if (!is_null($key)) {
            Session::getInstance()->getFiles()->_get()['session']['validator']['form-data'][$key] = $ref;
        }
    }

    /**
     * @param string|null $key
     * @return bool|mixed
     */
    public function check(string $key = null){

        if (is_null($key)) {
            $key = Input::getInstance()->get('X-Track-Token');
            if (empty($key)) $key = Input::getInstance()->get('track');
        }

        if (empty($key)) return false;

        $ref = Session::getInstance()->getFiles()->get([
            'session' => [
                'validator' => [
                    'form-data' => $key
                ]
            ]
        ]);

        if (!$ref) return false;

        return $ref;
    }
}