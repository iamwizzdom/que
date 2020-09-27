<?php

namespace que\security;

use que\http\input\Input;

abstract class Middleware extends MiddlewareResponse implements interfaces\Middleware
{
    private interfaces\Middleware $next;

    public function setNext(interfaces\Middleware $next): interfaces\Middleware
    {
        // TODO: Implement setNext() method.
        $this->next = $next;
        return $next;
    }

    public function handle(Input $input): MiddlewareResponse
    {
        // TODO: Implement handle() method.
        if (!$this->next) {
            $this->setAccess(true);
            return $this;
        }
        return $this->next->handle($input);
    }
}