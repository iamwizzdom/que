<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 6/5/2020
 * Time: 11:35 AM
 */

namespace que\security\interfaces;


use que\http\input\Input;
use que\security\MiddlewareResponse;

interface Middleware
{

    /**
     * @param Input $input
     * @param MiddlewareResponse $response
     * @return mixed
     */
    public function handle(Input $input, MiddlewareResponse $response);
}