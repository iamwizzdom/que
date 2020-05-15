<?php

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

class App implements \que\common\structure\Api
{

    /**
     * This method will run when the module is accessed
     * @param array $uri_args - This parameter provides the arguments found in the uri
     * @note Que will run this method for you automatically
     * @return array|Json|Jsonp|Html|Plain - This method must return an array or a valid Que HTTP response object
     * @recommendation When returning an array, the returned array should have an index 'code' which will define
     * the HTTP response code (optional)
     */
    public function process(array $uri_args)
    {
        // TODO: Implement process() method.

        return http()->output()->json([], 201, [], false, JSON_PRETTY_PRINT);
    }
}