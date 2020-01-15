<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 8/30/2019
 * Time: 7:35 PM
 */

namespace que\utility\pattern;

interface Observable {
    public function addObserver($observer);
}