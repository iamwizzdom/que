<?php

use que\common\structure\Api;
use que\http\input\Input;
use que\http\output\response\Html;
use que\http\output\response\Json;
use que\http\output\response\Jsonp;
use que\http\output\response\Plain;

/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/22/2020
 * Time: 1:07 AM
 */

class App implements Api
{
    /**
     * This method will run when the module is accessed
     * @param Input $input
     * @return array|Json|Jsonp|Html|Plain - This method must return an array or a valid Que HTTP response object
     * @note Que will run this method for you automatically
     * @recommendation When returning an array, the returned array should have an index 'code' which will define
     * the HTTP response code (optional)
     */
    public function process(Input $input): array|Json|Jsonp|Html|Plain
    {
        // TODO: Implement process() method.
        return http()->output()->json($input['route.params'], 201, JSON_PRETTY_PRINT);
    }


}