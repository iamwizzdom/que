<?php

use que\common\manager\Manager;
use que\common\structure\Page;
use que\template\Composer;

/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/16/2020
 * Time: 1:16 AM
 */

class View extends Manager implements Page
{

    /**
     * This method will run when the module is accessed via GET request
     * @param array $uri_args - This parameter provides the arguments found in the uri
     * @note Que will run this method for you automatically
     */
    public function onLoad(array $uri_args): void
    {
        // TODO: Implement onLoad() method.
        current_route()->setTitle('Welcome Que');

        $this->composer()->data([
            'hello' => 'Hello world'
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
        $composer->setTmpFileName('module/view.html');
        $composer->prepare()->renderWithSmarty();
    }
}