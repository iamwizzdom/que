<?php

namespace que\security;

use Closure;
use que\http\input\Input;
use que\security\interfaces\Middleware;

abstract class ResourceMiddleware implements Middleware
{
    /**
     * @param Input $input
     * @param Closure $next
     * @return bool
     */
    abstract public function handle(Input $input, Closure $next): bool;

}