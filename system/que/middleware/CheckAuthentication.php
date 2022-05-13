<?php


namespace que\middleware;


use Closure;
use que\common\exception\QueException;
use que\http\HTTP;
use que\http\input\Input;
use que\route\Route;
use que\security\GlobalMiddleware;

class CheckAuthentication extends GlobalMiddleware
{

    /**
     * @throws QueException
     */
    public function handle(Input $input, Closure $next): mixed
    {
        // TODO: Implement handle() method.
        $route = Route::getCurrentRoute();

        if ($route->isRequireLogin() === true && !is_logged_in()) {

            if (!empty($route->getRedirectUrl())) {

                redirect($route->getRedirectUrl(), [
                    [
                        'message' => sprintf("You don't have access to this route (%s), login and try again.", current_url()),
                        'status' => INFO
                    ]
                ]);

            } else {

                throw new QueException(
                    "You don't have access to the current route, login and try again.",
                    "Access Denied", HTTP::UNAUTHORIZED
                );
            }

        } elseif ($route->isRequireLogin() === false && is_logged_in()) {

            if (!empty($route->getRedirectUrl())) {

                redirect($route->getRedirectUrl(), [
                    [
                        'message' => sprintf("You don't have access to this route (%s), logout and try again.",
                            current_url()),
                        'status' => INFO
                    ]
                ]);

            } else {

                throw new QueException(
                    "You don't have access to the current route, logout and try again.",
                    "Access Denied", HTTP::UNAUTHORIZED
                );

            }

        }
        return $next();
    }
}