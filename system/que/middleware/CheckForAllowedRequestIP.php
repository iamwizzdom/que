<?php


namespace que\middleware;


use Closure;
use que\common\exception\QueException;
use que\http\HTTP;
use que\http\input\Input;
use que\http\request\Request;
use que\route\Route;
use que\security\GlobalMiddleware;

class CheckForAllowedRequestIP extends GlobalMiddleware
{

    /**
     * @throws QueException
     */
    public function handle(Input $input, Closure $next): mixed
    {
        // TODO: Implement handle() method.
        $route = Route::getCurrentRoute();

        $ips = $route->getAllowedIPs();

        if (!empty($ips) && !in_array($ip = Request::getClientIp(), $ips)) {
            throw new QueException(
                "Your IP address [{$ip}] is not allowed to access this route.",
                "Unauthorized Request IP", HTTP::UNAUTHORIZED
            );
        }
        return $next();
    }
}