<?php

namespace que\utility\structure\lists;

/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 6/30/2019
 * Time: 11:48 PM
 */

class ListNode
{
    /**
     * Data to hold
     */
    public $data;

    /**
     * Link to next node
     */
    public $next;

    /**
     * Link to previous node
     */
    public $previous;


    /**
     * ListNode constructor.
     * @param $data
     */
    function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Read data
     * @return mixed
     */
    function readNode()
    {
        return $this->data;
    }
}