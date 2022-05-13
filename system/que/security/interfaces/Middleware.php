<?php

namespace que\security\interfaces;

use Closure;
use que\http\input\Input;

interface Middleware
{
    /**
     * @param Input $input
     * @param Closure $next
     * @return mixed
     */
    public function handle(Input $input, Closure $next): mixed;
}