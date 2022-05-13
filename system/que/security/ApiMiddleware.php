<?php

namespace que\security;

use Closure;
use JsonSerializable;
use que\http\input\Input;
use que\http\output\response\Html;
use que\http\output\response\Json;
use que\http\output\response\Jsonp;
use que\http\output\response\Plain;
use que\security\interfaces\Middleware;

abstract class ApiMiddleware implements Middleware
{
    /**
     * @param Input $input
     * @param Closure $next
     * @return array|JsonSerializable|Json|Jsonp|Html|Plain
     */
    abstract public function handle(Input $input, Closure $next): array|JsonSerializable|Json|Jsonp|Html|Plain;

}