<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/10/2018
 * Time: 2:15 PM
 */

namespace que\database\model;


class CentralModel extends Model
{
    /**
     * @param $key
     * @param callable $callback
     */
    public function append($key, callable $callback) {
        $this->appends[$key] = $callback;
    }

    /**
     * @param array $appends
     */
    public function appendMulti(array $appends) {
        foreach ($appends as $key => $callback) {
            if (!is_callable($callback)) continue;
            $this->appends[$key] = $callback;
        }
    }
}
