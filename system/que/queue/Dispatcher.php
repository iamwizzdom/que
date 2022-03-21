<?php
/**
 * Created by PhpStorm.
 * User: wisdom Emenike
 * Date: 27/01/2022
 * Time: 9:57 PM
 */

namespace que\queue;

use que\cache\Cache;
use que\database\DB;

class Dispatcher
{
    private Cache $cache;

    public function __construct()
    {
        $this->cache = Cache::getInstance();
    }

    public function dispatch(Job $job) {
        $job->dispatchTime = APP_TIME;
        $this->cache->rPush("jobs_from_que", serialize($job));
    }

    public function dispatchNow(Job $job) {
        if (DB::hasOnGoingTrans() && $job->isAfterDbCommit()) {
            DB::addTransListener(function () use ($job) {
                $job->handle();
            });
        } else $job->handle();
    }
}