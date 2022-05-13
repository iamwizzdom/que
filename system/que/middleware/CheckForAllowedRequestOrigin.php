<?php


namespace que\middleware;


use Closure;
use que\common\exception\QueException;
use que\http\HTTP;
use que\http\input\Input;
use que\http\request\Request;
use que\route\Route;
use que\security\GlobalMiddleware;

class CheckForAllowedRequestOrigin extends GlobalMiddleware
{

    /**
     * @throws QueException
     */
    public function handle(Input $input, Closure $next): mixed
    {
        // TODO: Implement handle() method.
        $route = Route::getCurrentRoute();
        $origins = $route->getAllowedOrigins();

        if (!in_array('*', $origins) && !in_array($origin = Request::getOrigin(), $origins)) {
            $message = "The origin [{$origin}] of this request is not supported for this route.";
            if (!LIVE) $message .= " Supported origins: " . (implode(", ", $route->getAllowedOrigins()) ?: "None") . ".";
            throw new QueException($message, "Unsupported Request Origin", HTTP::NOT_ACCEPTABLE);
        }
        return $next();
    }
}