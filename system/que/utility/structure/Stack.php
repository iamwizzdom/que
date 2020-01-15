<?php

namespace que\utility\structure;

/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 6/31/2019
 * Time: 1:12 AM
 */

use RuntimeException;

class Stack
{
    /**
     * @var array
     */
    protected $stack;

    /**
     * @var int
     */
    protected $limit;

    /**
     * Stack constructor.
     * @param int $limit
     */
    public function __construct($limit = 10) {
        // initialize the stack
        $this->stack = array();
        // stack can only contain this many items
        $this->limit = $limit;
    }

    /**
     * @param $item
     */
    public function push($item) {
        // trap for stack overflow
        if (count($this->stack) < $this->limit) {
            // prepend item to the start of the array
            array_unshift($this->stack, $item);
        } else {
            throw new RunTimeException('Stack is full!');
        }
    }

    public function pop() {
        if ($this->isEmpty()) {
            // trap for stack underflow
            throw new RunTimeException('Stack is empty!');
        } else {
            // pop item from the start of the array
            return array_shift($this->stack);
        }
    }

    public function top() {
        return current($this->stack);
    }

    public function isEmpty() {
        return empty($this->stack);
    }
}