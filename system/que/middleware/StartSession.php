<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 10/2/2020
 * Time: 10:23 PM
 */

namespace que\middleware;


use Closure;
use que\http\input\Input;
use que\security\GlobalMiddleware;
use que\session\Session;

class StartSession extends GlobalMiddleware
{

    public function handle(Input $input, Closure $next): mixed
    {
        // TODO: Implement handle() method.
        Session::startSession();
        return $next();
    }
}