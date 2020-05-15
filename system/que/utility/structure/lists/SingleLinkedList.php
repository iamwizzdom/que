<?php

namespace que\utility\structure\lists;


/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 6/30/2019
 * Time: 11:51 PM
 *
 * Title: Single linked list
 * Description: Implementation of a single linked list
 */


class SingleLinkedList
{
    /**
     * Link to the first node in the list
     */
    private $firstNode;

    /**
     * Link to the last node in the list
     */
    private $lastNode;

    /**
     * Total nodes in the list
     */
    private $count;


    /**
     * LinkedList constructor.
     */
    function __construct()
    {
        $this->firstNode = null;
        $this->lastNode = null;
        $this->count = 0;
    }

    public function isEmpty()
    {
        return ($this->firstNode == null);
    }

    public function insertFirst($data)
    {
        $link = new ListNode($data);
        $link->next = $this->firstNode;
        $this->firstNode = &$link;

        // If this is the first node inserted in the list then set the lastNode pointer to it.
        if ($this->lastNode == null)
            $this->lastNode = &$link;

        $this->count++;
    }

    public function insertLast($data)
    {
        if ($this->firstNode != null) {
            $link = new ListNode($data);
            $this->lastNode->next = $link;
            $link->next = null;
            $this->lastNode = &$link;
            $this->count++;
        } else {
            $this->insertFirst($data);
        }
    }

    /**
     * @return null
     */
    public function deleteFirstNode()
    {
        $temp = $this->firstNode;
        $this->firstNode = $this->firstNode->next;
        if ($this->firstNode != null)
            $this->count--;

        return $temp;
    }

    public function deleteLastNode()
    {
        if ($this->firstNode != null) {
            if ($this->firstNode->next == null) {
                $this->firstNode = null;
                $this->count--;
            } else {
                $previousNode = $this->firstNode;
                $currentNode = $this->firstNode->next;

                while ($currentNode->next != null) {
                    $previousNode = $currentNode;
                    $currentNode = $currentNode->next;
                }

                $previousNode->next = null;
                $this->count--;
            }
        }
    }

    /**
     * @param $key
     */
    public function deleteNode($key)
    {
        $current = $this->firstNode;
        $previous = $this->firstNode;

        while ($current->data != $key) {
            if ($current->next == null)
                return;
            else {
                $previous = $current;
                $current = $current->next;
            }
        }

        if ($current == $this->firstNode) {
            if ($this->count == 1) {
                $this->lastNode = $this->firstNode;
            }
            $this->firstNode = $this->firstNode->next;
        } else {
            if ($this->lastNode == $current) {
                $this->lastNode = $previous;
            }
            $previous->next = $current->next;
        }
        $this->count--;
    }

    /**
     * @param $key
     * @return null
     */
    public function find($key)
    {
        $current = $this->firstNode;
        while ($current->data != $key) {
            if ($current->next == null)
                return null;
            else
                $current = $current->next;
        }
        return $current;
    }

    /**
     * @param $nodePos
     * @return null
     */
    public function readNode($nodePos)
    {
        if ($nodePos <= $this->count) {
            $current = $this->firstNode;
            $pos = 1;
            while ($pos != $nodePos) {
                if ($current->next == null)
                    return null;
                else
                    $current = $current->next;

                $pos++;
            }
            return $current->data;
        } else
            return null;
    }

    /**
     * @return int
     */
    public function totalNodes()
    {
        return $this->count;
    }

    /**
     * @return array
     */
    public function readList()
    {
        $listData = array();
        $current = $this->firstNode;

        while ($current != null) {
            array_push($listData, $current->readNode());
            $current = $current->next;
        }
        return $listData;
    }

    public function reverseList()
    {
        if ($this->firstNode != null) {
            if ($this->firstNode->next != null) {
                $current = $this->firstNode;
                $new = null;

                while ($current != null) {
                    $temp = $current->next;
                    $current->next = $new;
                    $new = $current;
                    $current = $temp;
                }
                $this->firstNode = $new;
            }
        }
    }
}