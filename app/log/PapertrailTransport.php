<?php

use que\error\log\LoggerTransport;

/**
 * Created by PhpStorm.
 * User: wisdom Emenike
 * Date: 09/02/2022
 * Time: 9:00 PM
 */

class PapertrailTransport extends LoggerTransport
{

    /**
     * @inheritDoc
     */
    public function log(): bool|int
    {
        // TODO: Implement log() method.
        return true;
    }
}