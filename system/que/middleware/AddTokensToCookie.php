<?php


namespace que\middleware;


use Closure;
use que\http\input\Input;
use que\security\CSRF;
use que\security\GlobalMiddleware;

class AddTokensToCookie extends GlobalMiddleware
{

    public function handle(Input $input, Closure $next): mixed
    {
        // TODO: Implement handle() method.
        if (!$input->getCookie()->_isset('XSRF-TOKEN')) {
            $csrf = CSRF::getInstance();
            $input->getCookie()->set("XSRF-TOKEN", $csrf->getToken(), $csrf->getExpiryTime());
        }
        return $next();
    }
}