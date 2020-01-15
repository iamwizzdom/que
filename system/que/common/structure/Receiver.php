<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 10/17/2019
 * Time: 2:57 PM
 */

namespace que\common\structure;


interface Receiver
{

    /**
     * This method will run when the module is accessed via POST request
     * @param array $uri_args - This parameter provides the arguments found in the uri
     * @note Que will run this method for you automatically
     */
    public function onReceive(array $uri_args): void;
}