<?php

namespace que\route\structure;


use Exception;
use ReflectionClass;

abstract class RouteImplementEnum
{
    const IMPLEMENT_ADD = -1;
    const IMPLEMENT_EDIT = 0;
    const IMPLEMENT_INFO = 1;
    const IMPLEMENT_PAGE = 2;
    const IMPLEMENT_API = 3;
    const IMPLEMENT_RESOURCE = 4;

    /**
     * @param int $needle
     * @return string
     */
    public static function getImplement(int $needle) {
        try {

            return array_search($needle, (new ReflectionClass(self::class))->getConstants()) ?: '';
        } catch (Exception $exception) {
            return '';
        }
    }
}