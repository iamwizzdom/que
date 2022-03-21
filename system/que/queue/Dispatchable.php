<?php
/**
 * Created by PhpStorm.
 * User: wisdom Emenike
 * Date: 27/01/2022
 * Time: 9:12 PM
 */

namespace que\queue;

trait Dispatchable
{
    /**
     * @return PendingDispatcher
     */
    public static function dispatch(): PendingDispatcher
    {
        return new PendingDispatcher(new static(...func_get_args()));
    }

    /**
     * @param bool $condition
     * @param ...$params
     * @return PendingDispatcher|null
     */
    public static function dispatchIf(bool $condition, ...$params): ?PendingDispatcher
    {
        return $condition ? new PendingDispatcher(new static(...$params)) : null;
    }
}