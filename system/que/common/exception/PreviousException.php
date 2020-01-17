<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/16/2020
 * Time: 12:44 AM
 */

namespace que\common\exception;

use Exception;

class PreviousException extends Exception
{

    /**
     * @param array $backtrace
     * @param int $index
     * @return PreviousException
     */
    public static function getInstance(array $backtrace, int $index = 0)
    {
        $e = new self();
        $e->file = ($backtrace[$index]['file'] ?? '');
        $e->line = ($backtrace[$index]['line'] ?? '');
        return $e;
    }

}