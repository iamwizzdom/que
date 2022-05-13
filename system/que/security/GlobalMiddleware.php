<?php

namespace que\security;

use Closure;
use JsonSerializable;
use que\http\input\Input;
use que\http\output\response\Json;
use que\http\output\response\Jsonp;
use que\security\interfaces\Middleware;
use que\template\Composer;

abstract class GlobalMiddleware implements Middleware
{
    /**
     * @param Input $input
     * @param Closure $next
     * @return mixed
     */
    abstract public function handle(Input $input, Closure $next): mixed;

}