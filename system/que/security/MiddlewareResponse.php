<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 6/5/2020
 * Time: 11:53 AM
 */

namespace que\security;

use JsonSerializable;
use que\http\HTTP;
use que\http\output\response\Html;
use que\http\output\response\Json;
use que\http\output\response\Jsonp;
use que\http\output\response\Plain;

abstract class MiddlewareResponse
{
    /**
     * @var bool
     */
    private ?bool $access = null;

    /**
     * @var string
     */
    private ?string $title = "Middleware Error";

    /**
     * @var string|array|JsonSerializable|Json|Jsonp|Html|Plain
     */
    private $response = null;

    /**
     * @var int
     */
    private int $responseCode = 0;

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
    protected function setAccess(bool $hasAccess): void
    {
        $this->access = $hasAccess;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    protected function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string|array|JsonSerializable|Json|Jsonp|Html|Plain
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param string|array|JsonSerializable|Json|Jsonp|Html|Plain $response
     */
    protected function setResponse($response) {
        $this->response = $response;
    }

    /**
     * @return int
     */
    public function getResponseCode(): int
    {
        return $this->responseCode;
    }

    /**
     * @param int $responseCode
     */
    protected function setResponseCode(int $responseCode): void
    {
        $this->responseCode = $responseCode;
    }
}
