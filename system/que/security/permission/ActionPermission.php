<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 11/16/2019
 * Time: 12:20 PM
 */

namespace que\security\permission;


interface ActionPermission
{
    /**
     * This method must return a bool which will be used to determine
     * if permission was granted or not
     * @param int $actionID
     * @return bool
     */
    public function hasPermission(int $actionID): bool;
}