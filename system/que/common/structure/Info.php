<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 8/10/2018
 * Time: 12:27 PM
 */

namespace que\common\structure;

use que\model\Model;
use que\template\Composer;

interface Info
{
    /**
     * This method will run each time the module is accessed
     * @param array $uri_args - This parameter provides the arguments found in the uri
     * @note Que will run this method for you automatically
     * @return Model|null - This method must return a model of the needed record or null
     */
    public function info(array $uri_args): ?Model;

    /**
     * This method will run when the module is accessed via GET request
     * @param array $uri_args - This parameter provides the arguments found in the uri
     * @param Model|null $info - This parameter will provide the data returned by $this->info()
     * @note Que will run this method for you automatically
     */
    public function onLoad(array $uri_args, ?Model $info): void;

    /**
     * This method will run when the module is accessed via POST request
     * @param array $uri_args - This parameter provides the arguments found in the uri
     * @param Model|null $info - This parameter will provide the data returned by $this->info()
     * @note Que will run this method for you automatically
     */
    public function onReceive(array $uri_args, ?Model $info): void;

    /**
     * This method will run last, to finalize your Composer and render your template
     * @param Composer $composer
     * @note Que will run this method for you automatically
     */
    public function setTemplate(Composer $composer): void;

}