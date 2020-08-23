<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 10/17/2019
 * Time: 2:57 PM
 */

namespace que\common\structure;


use que\database\interfaces\model\Model;
use que\http\input\Input;

interface Receiver
{

    /**
     * This method will run when the module is accessed via any request method other than a GET request
     * @param Input $input
     * @param Model|null $info - This parameter will provide the data returned by $this->info() if the info method exist otherwise null
     * @note Que will run this method for you automatically
     */
    public function onReceive(Input $input, ?Model $info = null): void;
}