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
    /**
     * @return array
     */
    public function array_keys(): array;

    /**
     * @return array
     */
    public function array_values(): array;

    /**
     * @return int|string|null
     */
    public function key(): int|string|null;

    /**
     * @return mixed
     */
    public function current(): mixed;

    public function shuffle(): void;
}
