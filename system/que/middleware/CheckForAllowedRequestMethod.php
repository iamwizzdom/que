<?php


namespace que\middleware;


use Closure;
use que\common\exception\QueException;
use que\http\HTTP;
use que\http\input\Input;
use que\http\request\Request;
use que\route\Route;
use que\security\GlobalMiddleware;

class CheckForAllowedRequestMethod extends GlobalMiddleware {

    /**
     * @throws QueException
     */
    public function handle(Input $input, Closure $next): mixed
    {
        // TODO: Implement handle() method.
        $route = Route::getCurrentRoute();

        if (!in_array($method = Request::getMethod(), $route->getAllowedMethods())) {
            $message = "The {$method} request method is not supported for this route.";
            if (!LIVE) $message .= " Supported methods: " . (implode(", ", $route->getAllowedMethods()) ?: "None") . ".";
            throw new QueException($message, "Unsupported Request Method", HTTP::METHOD_NOT_ALLOWED);
        }
        return $next();
    }
}