<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 5/9/2020
 * Time: 12:53 AM
 */

namespace que\database\interfaces\observer;


use que\database\interfaces\model\Model;
use que\database\model\ModelCollection;
use que\database\observer\ObserverSignal;

interface Observer
{
    /**
     * Observer constructor.
     * @param ObserverSignal $signal
     */
    public function __construct(ObserverSignal $signal);

    /**
     * @return ObserverSignal
     */
    public function getSignal(): ObserverSignal;
}
