<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 3/30/2019
 * Time: 2:33 AM
 */

namespace que\security;

use Exception;
use que\common\exception\QueException;
use que\http\request\Request;
use que\session\type\Files;
use que\session\Session;
use que\support\Arr;
use que\utility\hash\Hash;

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

    public function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

    /**
     * @return CSRF
     */
    public static function getInstance(): CSRF
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param string $token
     * @return bool
     * @throws QueException
     */
    public function validateToken(string $token): bool
    {

        if (empty($token) && Request::getMethod() == Request::METHOD_GET) {
            return true;
        }

        if (strcmp($token, $this->getToken()) != 0) {
            if (empty($token)) throw new QueException('Cross Site Request Forgery (CSRF) are forbidden. [No Token Passed]', 'CSRF Error');
            $decodedToken = $this->decodeToken($token);
            if (!$this->isValidToken($decodedToken)) throw new QueException('Cross Site Request Forgery (CSRF) are forbidden. [Invalid Token]', 'CSRF Error');
            if (!$this->isValidSignature($decodedToken)) throw new QueException('Cross Site Request Forgery (CSRF) are forbidden. [Invalid Signature]', 'CSRF Error');
            if ($this->isExpiredToken($decodedToken)) throw new QueException('Cross Site Request Forgery (CSRF) are forbidden. [Expired Token]', 'CSRF Error');
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function getToken(): mixed
    {
        return $this->session->get("csrf.token", '');
    }

    /**
     * @return int
     */
    public function getExpiryTime(): int
    {
        return (APP_TIME + TIMEOUT_TEN_MIN);
    }

    /**
     * @return $this
     */
    public function generateToken(): static
    {

        $data = [
            'signature' => $this->getSignature(),
            'unique' => unique_id(),
            'expire' => $this->getExpiryTime(),
        ];

        $data['hash'] = Hash::sha(json_encode($data));
        $token = base64_encode(json_encode($data));

        $this->session->set("csrf.token", ("csrf:" . wordwrap($token, 4, ":", true)));
        return $this;
    }

    private function getSignature(): string
    {
        return Hash::sha(sprintf("%s-%s-%s", config("auth.app.salt", APP_PACKAGE_NAME), Session::getSessionID(),
            (is_logged_in() ? user(config('database.tables.user.primary_key', 'id')) : '')));
    }

    private function decodeToken(string $token)
    {
        $token = base64_decode(str_strip(str_start_from($token, "csrf:") ?: '', ":") ?: '');
        return $token ? json_decode($token, true) : false;
    }

    private function isValidToken($decodedToken): bool
    {
        return $decodedToken &&
            (
                isset($decodedToken['signature']) &&
                isset($decodedToken['expire']) &&
                isset($decodedToken['unique']) &&
                isset($decodedToken['hash'])
            ) && strcmp($decodedToken['hash'], Hash::sha(json_encode(Arr::exclude($decodedToken, 'hash')))) == 0;
    }

    private function isValidSignature($decodedToken): bool
    {
        return $decodedToken && (strcmp(($decodedToken['signature'] ?? ''), $this->getSignature()) == 0);
    }

    private function isExpiredToken($decodedToken): bool
    {
        return $decodedToken && (($decodedToken['expire'] ?? 0) < APP_TIME);
    }

}
