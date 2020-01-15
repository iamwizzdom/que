<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 10/5/2018
 * Time: 6:47 PM
 */

namespace que\common\structure;


interface Resource
{

    /**
     * This method will run when the module is accessed, to read/render the processed resource
     * @param array $uri_args - This parameter provides the arguments found in the uri
     * @note Que will run this method for you automatically
     */
    public function render(array $uri_args): void;

}