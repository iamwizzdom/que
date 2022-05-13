<?php


namespace que\middleware;


use Closure;
use que\common\exception\QueException;
use que\http\HTTP;
use que\http\input\Input;
use que\route\Route;
use que\security\GlobalMiddleware;

class CheckForMaintenanceMode extends GlobalMiddleware
{

    /**
     * @throws QueException
     */
    public function handle(Input $input, Closure $next): mixed
    {
        // TODO: Implement handle() method.
        if (Route::getCurrentRoute()->isUnderMaintenance()) {
            throw new QueException(
                "This route is currently under maintenance, please try again later.",
                "Maintenance Mode", HTTP::MAINTENANCE
            );
        }
        return $next();
    }
}