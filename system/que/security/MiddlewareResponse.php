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
    private string $message = '';

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
}
