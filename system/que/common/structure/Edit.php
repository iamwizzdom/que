<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 8/10/2018
 * Time: 12:27 PM
 */

namespace que\common\structure;

use que\database\interfaces\model\Model;
use que\http\input\Input;
use que\template\Composer;

interface Edit extends Receiver
{
    /**
     * This method will run each time the module is accessed
     * @param Input $input
     * @return Model|null - This method must return a model of the record being edited or null
     * @note Que will run this method for you automatically
     */
    public function info(Input $input): ?Model;

    /**
     * This method will run when the module is accessed via GET request
     * @param Input $input
     * @param Model|null $info - This parameter will provide the data returned by $this->info()
     * @note Que will run this method for you automatically
     */
    public function onLoad(Input $input, ?Model $info): void;

    /**
     * This method will run last, to finalize your Composer and render your template
     * @param Composer $composer
     * @note Que will run this method for you automatically
     */
    public function setTemplate(Composer $composer): void;

}