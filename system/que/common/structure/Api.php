<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 10/5/2018
 * Time: 6:47 PM
 */

namespace que\common\structure;


use que\http\output\response\Html;
use que\http\output\response\Json;
use que\http\output\response\Jsonp;
use que\http\output\response\Plain;

interface Api
{

    /**
     * This method will run when the module is accessed
     * @param array $uri_args - This parameter provides the arguments found in the uri
     * @note Que will run this method for you automatically
     * @return array|Json|Jsonp|Html|Plain - This method must return an array or a valid Que HTTP response object
     * @recommendation When returning an array, the returned array should have an index 'code' which will define
     * the HTTP response code (optional)
     */
    public function process(array $uri_args);

}