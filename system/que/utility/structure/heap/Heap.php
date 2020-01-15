<?php


namespace que\utility\pattern\heap;


class Heap
{
    /**
     * @var array
     */
    private $array;

    /**
     * @var int
     */
    private $size;

    /**
     * Heap constructor.
     */
    public function __construct()
    {
        $this->array = [];
        $this->size = 0;
    }

    // Remove item with max key
    public function remove()
    {
        $root = $this->array[0];
        // put last element into root
        $this->array[0] = $this->array[--$this->size];
        $this->bubbleDown(0);
        return $root;
    }

    // Shift process
    public function bubbleDown($index)
    {
        $larger_Child = null;
        $top = $this->array[$index]; // save root
        while ($index < (int)($this->size / 2)) { // not on bottom row
            $leftChild = 2 * $index + 1;
            $rightChild = $leftChild + 1;

            // find larger child
            if ($rightChild < $this->size
                && $this->array[$leftChild] < $this->array[$rightChild]) // right child exists?
            {
                $larger_Child = $rightChild;
            } else {
                $larger_Child = $leftChild;
            }

            if ($top->getKey() >= $this->array[$larger_Child]->getKey()) {
                break;
            }

            // shift child up
            $this->array[$index] = $this->array[$larger_Child];
            $index = $larger_Child; // go down
        }

        $this->array[$index] = $top; // root to index
    }

    public function insertAt($index, Node $newNode)
    {
        $this->array[$index] = $newNode;
    }

    public function incrementSize()
    {
        $this->size++;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function asArray()
    {
        $arr = array();
        for ($j = 0; $j < sizeof($this->array); $j++) {
            $arr[] = $this->array[$j]->getKey();
        }

        return $arr;
    }
}