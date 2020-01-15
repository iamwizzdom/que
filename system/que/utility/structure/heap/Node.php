<?php


namespace que\utility\pattern\heap;


class Node
{
    /**
     * Heap pointer
     * @var
     */
    private $pointer;

    public function __construct($key)
    {
        $this->pointer = $key;
    }

    public function getKey()
    {
        return $this->pointer;
    }
}