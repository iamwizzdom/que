<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 5/15/2020
 * Time: 2:27 PM
 */

namespace que\support\interfaces;


use ArrayAccess;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Serializable;

interface QueArrayAccess extends ArrayAccess, Countable, IteratorAggregate, Serializable, JsonSerializable
{
    public function array_keys(): array;
    public function array_values(): array;
    public function key();
    public function current();
    public function shuffle(): void;
}