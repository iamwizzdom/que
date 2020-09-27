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
     * @param Middleware $next
     * @return Middleware
     */
    public function setNext(Middleware $next): Middleware;

    /**
     * @param Input $input
     * @return MiddlewareResponse
     */
    public function handle(Input $input): MiddlewareResponse;
}