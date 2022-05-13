<?php


namespace que\middleware;


use Closure;
use que\common\validator\Track;
use que\http\input\Input;
use que\route\Route;
use que\security\CSRF;
use que\security\GlobalMiddleware;

class AddTokensToHeaderResponse extends GlobalMiddleware
{


    public function handle(Input $input, Closure $next): mixed
    {
        // TODO: Implement handle() method.
        $route = Route::getCurrentRoute();

        if ($route->getType() === 'api' || $route->getType() === 'resource') {
            $input->getHeader()->set('X-Xsrf-Token', CSRF::getInstance()->getToken());
            $input->getHeader()->set('X-Track-Token', Track::generateToken());
        }
        return $next();
    }
}