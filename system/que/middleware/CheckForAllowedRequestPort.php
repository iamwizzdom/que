<?php


namespace que\middleware;


use Closure;
use que\common\exception\QueException;
use que\http\HTTP;
use que\http\input\Input;
use que\http\request\Request;
use que\route\Route;
use que\security\GlobalMiddleware;

class CheckForAllowedRequestPort extends GlobalMiddleware
{

    /**
     * @throws QueException
     */
    public function handle(Input $input, Closure $next): mixed
    {
        // TODO: Implement handle() method.
        $route = Route::getCurrentRoute();

        $ports = $route->getAllowedPorts();

        if (!empty($ports) && !in_array($port = Request::getPort(), $ports)) {
            throw new QueException(
                "You are not allowed to access this route on port [$port].",
                "Unsupported Request Port", HTTP::NOT_ACCEPTABLE
            );
        }
        return $next();
    }
}