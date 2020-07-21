<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 10/5/2018
 * Time: 6:47 PM
 */

namespace que\common\structure;


use que\http\input\Input;

interface Resource
{

    /**
     * This method will run when the module is accessed, to read/render the processed resource
     * @param Input $input
     * @note Que will run this method for you automatically
     */
    public function render(Input $input): void;

}