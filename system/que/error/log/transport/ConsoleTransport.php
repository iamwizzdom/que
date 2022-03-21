<?php
/**
 * Created by PhpStorm.
 * User: wisdom Emenike
 * Date: 10/02/2022
 * Time: 12:24 PM
 */

namespace que\error\log\transport;

use que\error\log\LoggerTransport;

class ConsoleTransport extends LoggerTransport
{

    public function log(): bool|int
    {
        // TODO: Implement log() method.

        $message = "\n[{$this->getTime()}]: {$this->getLabel()} : - {$this->getCLIColor($this->getType())} -> {$this->getCLIColor($this->getMessage())}";

        if (!LIVE) $message .= " in {$this->getFile()}:{$this->getLine()}";

        $message .= "\n";

        if (PHP_SAPI == 'cli') {
            echo $message;
            return true;
        }

        $fe = fopen('php://stderr', 'w');
        return fwrite($fe, $message) && fclose($fe);
    }
}