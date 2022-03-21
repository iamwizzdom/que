<?php
/**
 * Created by PhpStorm.
 * User: wisdom Emenike
 * Date: 27/01/2022
 * Time: 8:43 PM
 */

namespace que\queue;

abstract class Job
{
    use Dispatchable, Queueable;

    public abstract function handle();
}