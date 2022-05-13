<?php

namespace que\middleware;

use Closure;
use que\common\exception\QueException;
use que\http\HTTP;
use que\http\input\Input;
use que\http\request\Request;
use que\route\Route;
use que\security\CSRF;
use que\security\GlobalMiddleware;
use que\support\Arr;

class VerifyCsrfToken extends GlobalMiddleware
{

    /**
     * @param Input $input
     * @throws QueException
     */
    private function validateCSRF(Input $input) {

        $token = $input->getCookie()->get('XSRF-TOKEN', $input->get('X-Csrf-Token'));

        if (empty($token)) {
            foreach (
                [
                    'X-CSRF-TOKEN',
                    'x-csrf-token',
                    'X-XSRF-TOKEN',
                    'X-Xsrf-Token',
                    'x-xsrf-token',
                    'csrf-token',
                    'xsrf-token',
                    'Csrf-Token',
                    'Xsrf-Token',
                    'csrf',
                    'xsrf',
                    'Csrf',
                    'Xsrf',
                    'CSRF',
                    'XSRF'
                ] as $key
            ) if (!empty($token = $input->get($key))) break;
        }

        CSRF::getInstance()->validateToken(($token ?: ""));
    }

    /**
     * @throws QueException
     */
    public function handle(Input $input, Closure $next): mixed
    {
        // TODO: Implement handle() method.
        $route = Route::getCurrentRoute();

        if ($route->isForbidCSRF() === true && !Arr::includes($route->getIgnoredCRSFRequestMethods(), Request::getMethod())) {

            try {

                $this->validateCSRF($input);

            } catch (QueException $e) {
                CSRF::getInstance()->generateToken();
                $input->getCookie()->_unset('XSRF-TOKEN');
                throw new QueException($e->getMessage(), $e->getTitle(), HTTP::EXPIRED_AUTHENTICATION);
            }
        }

        CSRF::getInstance()->generateToken();
        $input->getCookie()->_unset('XSRF-TOKEN');
        return $next();
    }
}