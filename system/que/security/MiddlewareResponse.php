<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 6/5/2020
 * Time: 11:53 AM
 */

namespace que\security;


class MiddlewareResponse
{
    /**
     * @var bool
     */
    private ?bool $access = null;

    /**
     * @var string
     */
    private string $responseMessage = '';

    /**
     * @return bool|null
     */
    public function hasAccess(): ?bool
    {
        return $this->access;
    }

    /**
     * @param bool $access
     */
    public function setAccess(bool $access): void
    {
        $this->access = $access;
    }

    /**
     * @return string
     */
    public function getResponseMessage(): string
    {
        return $this->responseMessage;
    }

    /**
     * @param string $responseMessage
     */
    public function setResponseMessage(string $responseMessage): void
    {
        $this->responseMessage = $responseMessage;
    }
}