<?php

namespace que\security;

use Closure;
use que\http\input\Input;
use que\http\output\response\Html;
use que\http\output\response\Plain;
use que\security\interfaces\Middleware;
use que\template\Composer;

abstract class WebMiddleware implements Middleware
{
    /**
     * @param Input $input
     * @param Closure $next
     * @return Composer|Html|Plain
     */
    abstract public function handle(Input $input, Closure $next): Composer|Html|Plain;

}