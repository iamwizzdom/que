<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 8/30/2019
 * Time: 9:40 PM
 */

namespace que\utility\pattern;


interface Command
{
    public function onCommand($name, $args);
}