<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 6/5/2020
 * Time: 12:21 PM
 */

namespace app\middleware;

use Closure;
use JsonSerializable;
use que\http\HTTP;
use que\http\input\Input;
use que\http\output\response\Html;
use que\http\output\response\Json;
use que\http\output\response\Jsonp;
use que\http\output\response\Plain;
use que\security\ApiMiddleware;

class UserMiddleware extends ApiMiddleware
{

    public function handle(Input $input, Closure $next): array|JsonSerializable|Json|Jsonp|Html|Plain
    {
        // TODO: Implement handle() method.
        if (!is_logged_in()) {
            return http()->output()->json([
                'title' => "Auth Error",
                'message' => "Sorry, you must be logged in to access this route."
            ], HTTP::UNAUTHORIZED);
        }
        return $next();
    }
}
