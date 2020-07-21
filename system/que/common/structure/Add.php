<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 8/10/2018
 * Time: 12:28 PM
 */

namespace que\common\structure;

use que\http\input\Input;
use que\template\Composer;

interface Add extends Receiver
{

    /**
     * This method will run when the module is accessed via GET request
     * @param Input $input
     * @note Que will run this method for you automatically
     */
    public function onLoad(Input $input): void;

    /**
     * This method will run last, to finalize your Composer and render your template
     * @param Composer $composer
     * @note Que will run this method for you automatically
     */
    public function setTemplate(Composer $composer): void;

}