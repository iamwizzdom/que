<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/16/2020
 * Time: 12:44 AM
 */

namespace que\common\exception;

use Exception;
use Throwable;

class PreviousException extends Exception
{

    protected function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param int $backtrace_index
     * @param array|null $backtrace
     * @return PreviousException
     */
    public static function getInstance(int $backtrace_index = 0, array $backtrace = null): PreviousException
    {
        $backtrace = $backtrace ?: debug_backtrace();
        $e = new self();
        $e->file = ($backtrace[$backtrace_index]['file'] ?? '');
        $e->line = ($backtrace[$backtrace_index]['line'] ?? '');
        return $e;
    }

}