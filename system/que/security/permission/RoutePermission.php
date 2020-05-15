<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 11/16/2019
 * Time: 12:20 PM
 */

namespace que\security\permission;

use que\route\RouteEntry;

interface RoutePermission
{
    /**
     * This method must return a bool which will be used to determine
     * if permission was granted or not
     * @param RouteEntry $route - This parameter will provide the registered route entry
     * for which access permission is being tested
     * @return bool
     */
    public function hasPermission(RouteEntry $route): bool;
}