<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 6/5/2020
 * Time: 12:21 PM
 */

namespace app\middleware;


use que\http\input\Input;
use que\security\interfaces\Middleware;
use que\security\MiddlewareResponse;

class UserMiddleware implements Middleware
{

    /**
     * @inheritDoc
     */
    public function handle(Input $input, MiddlewareResponse $response)
    {
        // TODO: Implement handle() method.
        if (!$input->user()) {
            $response->setAccess(true);
            $response->setResponseMessage("Sorry, you must be logged in to access this route.");
        }
    }
}