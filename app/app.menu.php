<?php

use que\route\RouteEntry;
use que\security\permission\RoutePermission;

/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 11/16/2018
 * Time: 1:16 PM
 */

class Menu implements RoutePermission {

    public function menuList(): array {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function hasPermission(RouteEntry $route): bool
    {
        // TODO: Implement hasPermission() method.
        if ($route->isRequireLogIn() === true && !is_logged_in()) return false;
        return true;
    }
}