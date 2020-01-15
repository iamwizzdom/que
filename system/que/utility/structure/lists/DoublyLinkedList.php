<?php

namespace que\utility\structure\lists;

/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 6/31/2019
 * Time: 12:24 AM
 *
 * Title: Doubly linked list
 * Description: Implementation of a doubly linked list
 */


class DoublyLinkedList
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

    function __construct() {
        $this->firstNode = NULL;
        $this->lastNode = NULL;
        $this->count = 0;
    }

    public function isEmpty() {
        return ($this->firstNode == NULL);
    }

    public function insertFirst($data) {
        $newLink = new ListNode($data);

        if($this->isEmpty()) {
            $this->lastNode = $newLink;
        } else {
            $this->firstNode->previous = $newLink;
        }

        $newLink->next = $this->firstNode;
        $this->firstNode = $newLink;
        $this->count++;
    }


    public function insertLast($data) {
        $newLink = new ListNode($data);

        if($this->isEmpty()) {
            $this->firstNode = $newLink;
        } else {
            $this->lastNode->next = $newLink;
        }

        $newLink->previous = $this->lastNode;
        $this->lastNode = $newLink;
        $this->count++;
    }


    public function insertAfter($key, $data) {
        $current = $this->firstNode;

        while($current->data != $key) {
            $current = $current->next;

            if($current == NULL)
                return false;
        }

        $newLink = new ListNode($data);

        if($current == $this->lastNode) {
            $newLink->next = NULL;
            $this->lastNode = $newLink;
        } else {
            $newLink->next = $current->next;
            $current->next->previous = $newLink;
        }

        $newLink->previous = $current;
        $current->next = $newLink;
        $this->count++;

        return true;
    }


    public function deleteFirstNode() {

        $temp = $this->firstNode;

        if($this->firstNode->next == NULL) {
            $this->lastNode = NULL;
        } else {
            $this->firstNode->next->previous = NULL;
        }

        $this->firstNode = $this->firstNode->next;
        $this->count--;
        return $temp;
    }


    public function deleteLastNode() {

        $temp = $this->lastNode;

        if($this->firstNode->next == NULL) {
            $this->firtNode = NULL;
        } else {
            $this->lastNode->previous->next = NULL;
        }

        $this->lastNode = $this->lastNode->previous;
        $this->count--;
        return $temp;
    }


    public function deleteNode($key) {

        $current = $this->firstNode;

        while($current->data != $key) {
            $current = $current->next;
            if($current == NULL)
                return null;
        }

        if($current == $this->firstNode) {
            $this->firstNode = $current->next;
        } else {
            $current->previous->next = $current->next;
        }

        if($current == $this->lastNode) {
            $this->lastNode = $current->previous;
        } else {
            $current->next->previous = $current->previous;
        }

        $this->count--;
        return $current;
    }


    public function displayForward() {

        $current = $this->firstNode;

        while($current != NULL) {
            echo $current->readNode() . " ";
            $current = $current->next;
        }
    }


    public function displayBackward() {

        $current = $this->lastNode;

        while($current != NULL) {
            echo $current->readNode() . " ";
            $current = $current->previous;
        }
    }

    public function totalNodes() {
        return $this->count;
    }
}