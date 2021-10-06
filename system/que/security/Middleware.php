<?php

namespace que\security;

use que\http\input\Input;

abstract class Middleware extends MiddlewareResponse implements interfaces\Middleware
{
    private ?interfaces\Middleware $next = null;

    /**
     * @param interfaces\Middleware $next
     * @return interfaces\Middleware
     */
    final public function setNext(interfaces\Middleware $next): interfaces\Middleware
    {
        // TODO: Implement setNext() method.
        $this->next = $next;
        return $next;
    }

    /**
     * @param Input $input
     * @return MiddlewareResponse
     */
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