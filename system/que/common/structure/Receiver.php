<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 10/17/2019
 * Time: 2:57 PM
 */

namespace que\common\structure;


use que\database\model\interfaces\Model;

interface Receiver
{

    /**
     * This method will run when the module is accessed via POST request
     * @param array $uri_args - This parameter provides the arguments found in the uri
     * @param Model|null $info - This parameter will provide the data returned by $this->info() if the info method exist otherwise null
     * @note Que will run this method for you automatically
     */
    public function onReceive(array $uri_args, ?Model $info = null): void;
}