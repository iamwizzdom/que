<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 6/5/2020
 * Time: 11:53 AM
 */

namespace que\security;


use que\http\output\response\Html;
use que\http\output\response\Json;
use que\http\output\response\Jsonp;
use que\http\output\response\Plain;

class MiddlewareResponse
{
    /**
     * @var bool
     */
    private ?bool $access = null;

    /**
     * @var string
     */
    private string $message = '';

    /**
     * @var Json|Jsonp|Plain|Html|array
     */
    private $response = null;

    /**
     * @return bool|null
     */
    public function hasAccess(): ?bool
    {
        return $this->access;
    }

    /**
     * @param bool $hasAccess
     */
    public function setAccess(bool $hasAccess): void
    {
        $this->access = $hasAccess;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return Html|Json|Jsonp|Plain|array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Html|Json|Jsonp|Plain|array $response
     */
    public function setResponse($response) {
        $this->response = $response;
    }
}
