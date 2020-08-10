<?php

use que\common\manager\Manager;
use que\common\structure\Page;
use que\http\input\Input;
use que\template\Composer;

/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/16/2020
 * Time: 1:16 AM
 */

class View extends Manager implements Page, \que\security\interfaces\RoutePermission
{

    /**
     * This method will run when the module is accessed via GET request
     * @param Input $input
     * @note Que will run this method for you automatically
     */
    public function onLoad(Input $input): void
    {
        // TODO: Implement onLoad() method.
        current_route()->setTitle('Welcome Que');
        $this->composer()->data([
//            'hello' => 'Hello world, Welcome to Que'
            'hello' => 'Welcome to Que'
        ]);

    }

    /**
     * This method will run last, to finalize your Composer and render your template
     * @param Composer $composer
     * @note Que will run this method for you automatically
     */
    public function setTemplate(Composer $composer): void
    {
        // TODO: Implement setTemplate() method.
        $composer->setTmpFileName('module/view.tpl');
        $composer->prepare()->renderWithSmarty();
    }

    /**
     * @inheritDoc
     */
    public function hasPermission(\que\route\RouteEntry $route): bool
    {
        // TODO: Implement hasPermission() method.
        return true;
    }
}
