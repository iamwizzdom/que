<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 10/5/2018
 * Time: 6:47 PM
 */

namespace que\common\structure;


interface Api
{

    /**
     * This method will run when the module is accessed
     * @param array $uri_args - This parameter provides the arguments found in the uri
     * @note Que will run this method for you automatically
     * @return array - This method must return an array
     * @recommendation The returned array should have an index 'code' which will define the HTTP response code
     */
    public function process(array $uri_args): array;

}